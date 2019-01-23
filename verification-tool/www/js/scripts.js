if (!("autofocus" in document.createElement("input"))) {
    document.getElementById("serial-number").focus();
    document.getElementById("secret-hash").focus();
}