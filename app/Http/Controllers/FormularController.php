<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side proxy for the Google Apps Script "Gewinnbestätigung" form.
 *
 * WHY THIS EXISTS
 * ---------------
 * The form is published as a Google Apps Script web app (script.google.com).
 * Previously we embedded it in an <iframe> pointing straight at script.google.com.
 * That failed *intermittently on mobile*: when the phone is signed into one or
 * more Google accounts, the browser sends Google's cookies with the iframe
 * request and Google redirects to an account-specific path ("/u/0/", "/u/1/", ...).
 * Depending on the device's login state the form would sometimes load and
 * sometimes break (blank frame, "goog is not defined", third-party-cookie blocks).
 *
 * The only reliable fix is to make sure the *browser never contacts
 * script.google.com directly*. So here the Laravel server fetches the form
 * itself (no Google cookies => no "/u/N/" routing), extracts the actual form
 * markup, and serves it from our own domain. Submission is likewise proxied
 * through us to the script's doPost endpoint.
 */
class FormularController extends Controller
{
    /**
     * Render the signature form, proxied from Apps Script.
     */
    public function show(Request $request)
    {
        $token = $request->query('t', '');

        // Reject anything that isn't a token Apps Script would have issued.
        // Show our own local error page instead of bothering Google.
        if (! preg_match('/^[a-f0-9]{40,}$/i', $token)) {
            return view('formular');
        }

        $scriptUrl = config('services.signature_form.script_url');

        // Fetch SERVER-SIDE. This request carries no Google login cookies, so
        // Google cannot apply its multi-account "/u/N/" redirect — the root
        // cause of the flaky mobile behaviour.
        $response = Http::timeout(30)->get($scriptUrl, ['t' => $token]);

        if (! $response->successful()) {
            Log::warning('Formular: Apps Script request failed', ['status' => $response->status()]);
            abort(502, 'Formular konnte nicht geladen werden.');
        }

        // Apps Script does NOT return the form HTML directly. It returns a small
        // bootstrap page whose real content is hex-encoded inside a
        // goog.script.init("...") call, under the "userHtml" key. Pull that out
        // so we can serve only the form and skip Google's client runtime and its
        // nested sandbox iframes (which is what triggered the 404s on
        // /static/macros/... and the "goog is not defined" error).
        $formHtml = $this->extractUserHtml($response->body());

        if ($formHtml === null) {
            Log::warning('Formular: could not extract userHtml from Apps Script response');
            abort(502, 'Formular konnte nicht verarbeitet werden.');
        }

        // The form submits via google.script.run.submitSignature(payload), which
        // only works inside Google's runtime. Rewrite it to POST to our own proxy.
        $formHtml = $this->rewriteSubmitHandler($formHtml);

        return response($formHtml, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Proxy a form submission to the Apps Script doPost endpoint.
     *
     * Same principle as show(): the POST happens server-to-server, so no Google
     * cookies and no "/u/N/" routing are involved.
     */
    public function submit(Request $request)
    {
        $payload = $request->getContent();

        if ($payload === '') {
            return response()->json(['ok' => false, 'error' => 'No data received']);
        }

        $scriptUrl = config('services.signature_form.script_url');

        $response = Http::timeout(30)
            ->withBody($payload, 'application/json')
            ->post($scriptUrl);

        if ($response->body() === '') {
            return response()->json(['ok' => false, 'error' => 'No response from script']);
        }

        // Pass the script's JSON response straight back to the browser.
        return response($response->body(), $response->status(), [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Pull the "userHtml" form markup out of the Apps Script bootstrap page.
     *
     * The bootstrap page contains a single call:
     *     goog.script.init("<hex-encoded JSON>", ...)
     * The first string argument is a JSON object (with every quote encoded as
     * \x22, braces as \x7b, etc.). One of its keys, "userHtml", holds the actual
     * form HTML. Because every real quote inside is hex-encoded, the only literal
     * double-quotes in that argument are the opening/closing delimiters — which
     * makes the string easy to slice out.
     */
    private function extractUserHtml(string $wrapperHtml): ?string
    {
        $marker = 'goog.script.init(';
        $offset = strpos($wrapperHtml, $marker);

        if ($offset === false) {
            return null;
        }

        // Opening delimiter of the first argument.
        $start = strpos($wrapperHtml, '"', $offset);
        if ($start === false) {
            return null;
        }
        $start++;

        // Closing delimiter (next literal quote — all inner quotes are \x22).
        $end = strpos($wrapperHtml, '"', $start);
        if ($end === false) {
            return null;
        }

        $jsString = substr($wrapperHtml, $start, $end - $start);

        // The payload is a JS string literal using \xNN escapes. Convert those to
        // JSON-compatible \u00NN, then decode twice: first to unwrap the JS string
        // literal, then to parse the JSON object it represents.
        $jsString = preg_replace('/\\\\x([0-9a-fA-F]{2})/', '\\u00$1', $jsString);

        $innerJson = json_decode('"' . $jsString . '"');
        if (! is_string($innerJson)) {
            return null;
        }

        $data = json_decode($innerJson, true);
        if (! is_array($data) || ! isset($data['userHtml'])) {
            return null;
        }

        return $data['userHtml'];
    }

    /**
     * Replace the google.script.run.submitSignature(payload) chain with a fetch()
     * call to our own /formular/submit proxy, keeping the existing success/error
     * UI behaviour (message element + submitButton) intact.
     */
    private function rewriteSubmitHandler(string $html): string
    {
        $submitUrl = url('/formular/submit');

        $replacement = <<<JS
fetch('{$submitUrl}', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(result) {
          if (result.ok) {
            message.className = 'success';
            message.textContent = result.message || 'Vielen Dank.';
            document.getElementById('submitButton').disabled = true;
          } else {
            throw new Error(result.error || 'Unknown error');
          }
        })
        .catch(function(error) {
          message.className = 'error';
          message.textContent = error.message || String(error);
          document.getElementById('submitButton').disabled = false;
        });
JS;

        $rewritten = preg_replace(
            '/google\.script\.run[\s\S]*?\.submitSignature\s*\([^)]*\)\s*;?/',
            trim($replacement),
            $html,
            1,
            $count
        );

        if ($count === 0) {
            // The form markup didn't contain the expected call (e.g. Google
            // returned an error page for an invalid/expired token). Serve as-is.
            Log::info('Formular: submitSignature call not found; serving form unchanged');

            return $html;
        }

        return $rewritten;
    }
}
