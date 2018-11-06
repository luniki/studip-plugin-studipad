<h1>Bitte warten!</h1>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var iframe = document.createElement('iframe');
    iframe.style.visibility = 'hidden';
    iframe.onload = function (event) {
        console.log(event);
        window.location = "<?= htmlReady($padurl) ?>";
    };
    iframe.src = "<?= htmlReady($url) ?>";
    document.body.appendChild(iframe);
})
</script>
