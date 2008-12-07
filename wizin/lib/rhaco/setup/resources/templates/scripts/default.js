function resize_textarea(ev){
	var textarea = ev.target || ev.srcElement;
	var value = textarea.value;
	var lines = 1;
	for(var i = 0, l = value.length; i < l; i++){
		if(value.charAt(i) == '\n') lines++;
	}
	textarea.setAttribute("rows", lines);
}
