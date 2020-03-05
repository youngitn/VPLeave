function XmlHttpObj() {
    var XmlRequest = false;
    try {
        XmlRequest = new XMLHttpRequest();
    } catch (trymicrosoft) {
        try {
            XmlRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (othermicrosoft) {
            try {
                XmlRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                XmlRequest = false;
            }
        }
    }
    if (!XmlRequest) {
        alert("can't create XMLHttpRequest object.");
    }
    return XmlRequest;
};

function AJAXRequest() {
    var xmlObj = XmlHttpObj();
    if (!xmlObj) return false;
    var CBfunc, ObjSelf;
    ObjSelf = this;
    this.method = "POST";
    this.url;
    this.async = true;
    this.callback = function(cbobj) {
        return;
    };
    this.send = function() {
        if (!this.method || !this.url || !this.async) return false;
        xmlObj.open(this.method, this.url, this.async);
        if (this.method == "POST") xmlObj.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlObj.onreadystatechange = function() {
            if (xmlObj.readyState == 4) {
                if (xmlObj.status == 200) {
                    ObjSelf.callback(xmlObj);
                }
            }
        };
        if (this.method == "POST") xmlObj.send(this.content);
        else xmlObj.send(null);
    }
}