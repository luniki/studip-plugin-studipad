<iframe id="etherpad" src="<?= $controller->link_for('pads/open', $padid) ?>" style="width:100%"></iframe>
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
