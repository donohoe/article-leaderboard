var app = {
	init: function(){
		this.render();
	},
	render: function(){
		var response = window.response || {};
		if (response.rank) {
			var html = "";
			var len = response.rank.length;

			for (var i=0; i<len; i++) {
				var item = response.rank[i];
				var loadTime = Number(item["load-time"]).toFixed(2);
				var klass = (i<3) ? "fire" : "";
				var title = "";
				if (item["ads"] == 0) {
					klass += " noads";
					title = "No Ads";
				}

				var scoreChange = "";
				if (item["change"]["score"]) {
					var diff = parseInt( item["change"]["score"] - item["score"], 10);
					var dir = "up";
					var tri = "&#9650;";

					if ( parseInt(item["score"], 10) <= parseInt(item["change"]["score"], 10) ) {
						dir = "down";
						tri = "&#9660;";
					}
					scoreChange = "<super class='" + dir + "' title='Score is " + dir + " " + diff + " points from " + item["change"]["score"] + "'>" + tri + "</super>";
				}

				var score = item["score"];
				switch (true) {
					case (score > 99999):
						score = parseInt(score/1000, 10) + "K";
						break;
					case (score > 9999):
						score = (score/1000).toFixed(1) + "K";
						break;
					case (score > 999):
						score = (score/1000).toFixed(2) + "K";
						break;
				}

				html += [
					"<li class='" + klass + "' title='" + title + "'>",
						"<mark><a href='" + item["link"] + "' target='_blank' title='Open link'>" + item["label"] + "</a></mark>",
						"<small>",
							"<span class='loadTime' title='" + loadTime + " seconds'>" + loadTime + "</span>",
							"<span class='requests'>"  + item["requests"]   + "</span>",
							"<span class='speedIndex'>" + item["speed-index"] + "</span>",
							"<span class='visuallyComplete' style='display: none;'>" + item["visually-complete"] + "</span>",
							"<span class='score' title='Load Time * Speed Index * Page Size * Visually Complete = " + item["score"] + "'>" + scoreChange + "<b>" + score + "</b></span>",
						"</small>",
					"</li>"
				].join("");
			}
			document.getElementById("rankings").innerHTML = html;
			document.getElementById("updated").innerHTML = response.info.updated;
		}
	}
};

document.addEventListener('DOMContentLoaded', function(){
	app.init();
}, false);
