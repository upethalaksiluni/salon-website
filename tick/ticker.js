	function tick(){
		$('#ticker_01 li:first').slideUp( function () { $(this).appendTo($('#ticker_01')).slideDown(); });
	}
	setInterval(function(){ tick () }, 6000);
	 $.ajax ({
		 success: function(data){
		 	if (!data.results){
		 		return false;
		 	}
		 	for( var i in data.results){
		 		var result = data.results[i];
		 		var $res = $("<li />");
		 		$res.append(result.text);
		 	}
		 }
	});