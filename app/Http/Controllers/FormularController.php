<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FormularController extends Controller
{
    public function show(Request $request)
    {
        $token = $request->query('t', '');

        if (! preg_match('/^[a-f0-9]{40,}$/i', $token)) {
            return view('formular');
        }

        $scriptUrl = config('services.signature_form.script_url');
        $response = Http::timeout(30)
            ->withHeaders(['Accept' => 'text/html'])
            ->get($scriptUrl, ['t' => $token]);

        if (! $response->successful()) {
            abort(502, 'Formular konnte nicht geladen werden.');
        }

        $html = $this->rewriteSubmitHandler($response->body());

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function submit(Request $request)
    {
        $payload = $request->getContent();

        if ($payload === '') {
            return response()->json(['ok' => false, 'error' => 'No data received']);
        }

        $scriptUrl = config('services.signature_form.script_url');
        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody($payload, 'application/json')
            ->post($scriptUrl);

        if ($response->body() === '') {
            return response()->json(['ok' => false, 'error' => 'No response from script']);
        }

        return response($response->body(), $response->status(), [
            'Content-Type' => 'application/json',
        ]);
    }

    private function rewriteSubmitHandler(string $html): string
    {
        $submitUrl = url('/formular/submit');
        $replacement = <<<JS
fetch('{$submitUrl}', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(payload)
        })
        .then(r => r.json())
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
            '/google\.script\.run[\s\S]*?\.submitSignature\s*\(\s*payload\s*\)\s*;/',
            trim($replacement),
            $html,
            1,
            $count
        );

        if ($count === 0) {
            $rewritten = str_replace(
                "google.script.run\n        .withSuccessHandler(function(result) {\n          message.className = 'success';\n          message.textContent = result.message || 'Vielen Dank.';\n          document.getElementById('submitButton').disabled = true;\n        })\n        .withFailureHandler(function(error) {\n          message.className = 'error';\n          message.textContent = error.message || String(error);\n          document.getElementById('submitButton').disabled = false;\n        })\n        .submitSignature(payload);",
                trim($replacement),
                $html
            );
        }

        return $rewritten ?? $html;
    }
}
