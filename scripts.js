function update_square(div_id){
	var xreq=new XMLHttpRequest();
	var x=document.getElementById(div_id);
  var square=div_id.replace(/\D/g, '');

  if (x.classList.contains('active')) {
    // true
    var update_val=0;
  } else {
    var update_val=1;
  }
  var vars = "update_square=" + square + "&update_val=" + update_val;

	xreq.open("POST", "index.php", true);
	xreq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xreq.onreadystatechange = function() {
		if(xreq.readyState == 4 && xreq.status == 200) {
      x.classList.toggle("active"); 
		}
	}
	xreq.send(vars);
};
