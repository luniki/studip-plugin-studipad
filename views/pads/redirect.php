<h1>Bitte warten!</h1>
<?= $close ? "schließen":"nicht schließen"?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var iframe = document.createElement('iframe');
    iframe.style.visibility = 'hidden';
    iframe.onload = function (event) {
        window.location = <?= json_encode((string) $padurl) ?>;
    };
    iframe.src = <?= json_encode((string) $url) ?>;
    document.body.appendChild(iframe);
})
</script>
