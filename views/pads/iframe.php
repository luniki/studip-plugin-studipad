<iframe id="etherpad" src="<?= $controller->link_for('pads/open', $pad) ?>" style="width:100%"></iframe>
<script>
jQuery(window).on('message onmessage', function (e) {
    var msg = e.originalEvent.data;
    if (msg.name === 'ep_resize') {
        var width = msg.data.width;
        var height = msg.data.height;
        jQuery("#etherpad").height(height)
    }
});
</script>
