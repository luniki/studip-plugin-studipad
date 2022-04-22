var height;

window.onload = () => sendPostMessage();
window.onresize = () => sendPostMessage();

function getOuterHeight(el) {
    var height = Math.max(el.scrollHeight, el.offsetHeight, el.clientHeight);
    var style = getComputedStyle(el);

    height += parseInt(style.marginTop) + parseInt(style.marginBottom);
    return height;
}

function sendPostMessage() {
    var outerHeight = getOuterHeight(document.body)
    if (height !== outerHeight)  {
        height = outerHeight;
        window.parent.postMessage({
            name: "studip-plugin-project-page/resize",
            height: outerHeight,
        }, '*');
        console.debug("sent resize message");
    }
}
