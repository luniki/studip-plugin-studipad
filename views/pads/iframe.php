<iframe id="etherpad" src="<?= $controller->getPadLink('open') ?>" style="width:100%; min-width: 50vh; border: none;"></iframe>
<script>
jQuery(window).on('message onmessage', function (e) {
    var msg = e.originalEvent.data;
    if (msg.name === 'ep_resize') {
        var width = msg.data.width;
        var height = msg.data.height;
        jQuery("#etherpad").height(Math.min(Math.max(height, 400), 1080))
    }
});
</script>
