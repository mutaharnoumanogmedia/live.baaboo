@php
    use App\Models\Setting;
    $enabled = Setting::get('gtm_enabled', false);
    $output = '';
    if ($enabled && isset($part)) {
        $containerId = trim(Setting::get('gtm_container_id', ''));
        if ($part === 'head') {
            $headScript = trim(Setting::get('gtm_head_script', ''));
            if ($headScript !== '') {
                $output = $headScript;
            } elseif ($containerId !== '') {
                $output = '<!-- Google Tag Manager --><script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);})(window,document,\'script\',\'dataLayer\',\'' . e($containerId) . '\');</script><!-- End Google Tag Manager -->';
            }
        } elseif ($part === 'body') {
            $bodyScript = trim(Setting::get('gtm_body_script', ''));
            if ($bodyScript !== '') {
                $output = $bodyScript;
            } elseif ($containerId !== '') {
                $output = '<!-- Google Tag Manager (noscript) --><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . e($containerId) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript><!-- End Google Tag Manager (noscript) -->';
            }
        }
    }
@endphp
@if (!empty($output))
{!! $output !!}
@endif
