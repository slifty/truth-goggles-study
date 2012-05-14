<html>
	<head>
		<title>Truth Goggles User Study Results Summary</title>
		<link rel="stylesheet" href="styles/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<link rel="stylesheet" href="styles/analysis.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript" charset="utf-8"></script>
		
		<script type="text/javascript">
			var treatments = {};
			var ideology = {
				unspecified: 0,
				strong_conservative: 0,
				moderate_conservative: 0,
				independent: 0,
				moderate_liberal: 0,
				strong_liberal: 0,
				unspecified_percent: function() { return this.unspecified?Math.round(this.unspecified / phase_breakdown.complete * 1000)/10:0; },
				strong_conservative_percent: function() { return this.strong_conservative?Math.round(this.strong_conservative / phase_breakdown.complete * 1000)/10:0; },
				moderate_conservative_percent: function() { return this.moderate_conservative?Math.round(this.moderate_conservative / phase_breakdown.complete * 1000)/10:0; },
				independent_percent: function() { return this.independent?Math.round(this.independent / phase_breakdown.complete * 1000)/10:0; },
				moderate_liberal_percent: function() { return this.moderate_liberal?Math.round(this.moderate_liberal / phase_breakdown.complete * 1000)/10:0; },
				strong_liberal_percent: function() { return this.strong_liberal?Math.round(this.strong_liberal / phase_breakdown.complete * 1000)/10:0; }
			};
			var phase_breakdown = {
				omit: 0,
				pre: 0,
				mid: 0,
				post: 0,
				complete: 0,
				total: 0,
				omit_percent: function() { return Math.round(this.omit / this.total * 1000)/10; },
				pre_percent: function() { return Math.round(this.pre / this.total * 1000)/10; },
				mid_percent: function() { return Math.round(this.mid / this.total * 1000)/10; },
				post_percent: function() { return Math.round(this.post / this.total * 1000)/10; },
				complete_percent: function() { return Math.round(this.complete / this.total * 1000)/10; }
			};
			
			$(function() {
				$.ajax({
					method: "GET",
					url: "analysis_json.php",
					dataType: "json",
					success: function(data) {
						phase_breakdown["total"] = parseInt(data.total);
						phase_breakdown["omit"] = parseInt(data.omitted);
						var participants = data.participants;
						for(var x = 0; x < participants.length ; ++x) {
							var participant = participants[x];
							var stage = participant.stage
							var stage_progress = participant.stage_progress;
							
							if(stage < 8) {
								if(stage < 4 || (stage == 4 && stage_progress <= 2))
									phase_breakdown["pre"]++;
								else if(stage == 4 && stage_progress > 2)
									phase_breakdown["mid"]++;
								else
									phase_breakdown["post"]++;
								continue;
							}
							
							switch(parseInt(participant.ideology)) {
								case 0:
									ideology["unspecified"]++;
									break;
								case 1: 
									ideology["strong_conservative"]++;
									break;
								case 2: 
									ideology["moderate_conservative"]++;
									break;
								case 3: 
									ideology["independent"]++;
									break;
								case 4: 
									ideology["moderate_liberal"]++;
									break;
								case 5: 
									ideology["strong_liberal"]++;
									break;
							}
							
							phase_breakdown["complete"]++;
							
							var claims = participant.claims;
							for(var y  = 0 ; y < claims.length ; ++y) {
								if(isNaN(claims[y].response_1) || isNaN(claims[y].response_2))
									continue;
								var claim = claims[y].claim;
								var pre = parseInt(claims[y].response_1);
								var post = parseInt(claims[y].response_2);
								var real = parseInt(claim.verdict);
								
								var treatment = claims[y].treatment;
								if(treatments[treatment] == undefined) {
									treatments[treatment] = {
										total: 0,
										max_distance: 0,
										pre_total_distance: 0, // the total number of units deviated
										pre_over_max: 0, 
										pre_over_count: 0, 
										pre_over_distance: 0,
										pre_over_max_distance: 0,
										pre_under_max: 0, 
										pre_under_count: 0, 
										pre_under_distance: 0,
										pre_under_max_distance: 0,
										pre_exact_count: 0, 
										pre_over_percent: function(){return Math.round(this.pre_over_count / this.total * 100);}, 
										pre_over_saturation: function(){return Math.round(this.pre_over_count / this.pre_over_max * 100);}, 
										pre_under_percent: function(){return Math.round(this.pre_under_count / this.total * 100);}, 
										pre_under_saturation: function(){return Math.round(this.pre_under_count / this.pre_under_max * 100);}, 
										pre_exact_percent: function(){return Math.round(this.pre_exact_count / this.total * 100);}, 
										pre_inaccuracy_saturation: function(){return Math.round(this.pre_total_distance / this.max_distance * 100);},
										pre_over_bias_saturation: function(){return Math.round(this.pre_over_distance / this.pre_over_max_distance * 100);},
										pre_under_bias_saturation: function(){return Math.round(this.pre_under_distance / this.pre_under_max_distance * 100);},
										post_total_distance: 0,
										post_over_max: 0,
										post_over_count: 0,
										post_over_distance: 0,
										post_over_max_distance: 0,
										post_under_max: 0,
										post_under_count: 0,
										post_under_distance: 0,
										post_under_max_distance: 0,
										post_exact_count: 0,
										post_over_percent: function(){return Math.round(this.post_over_count / this.total * 100);}, 
										post_over_saturation: function(){return Math.round(this.post_over_count / this.post_over_max * 100);}, 
										post_under_percent: function(){return Math.round(this.post_under_count / this.total * 100);}, 
										post_under_saturation: function(){return Math.round(this.post_under_count / this.post_under_max * 100);}, 
										post_exact_percent: function(){return Math.round(this.post_exact_count / this.total * 100);}, 
										post_inaccuracy_saturation: function(){return Math.round(this.post_total_distance / this.max_distance * 100);},
										post_over_bias_saturation: function(){return Math.round(this.post_over_distance / this.post_over_max_distance * 100);},
										post_under_bias_saturation: function(){return Math.round(this.post_under_distance / this.post_under_max_distance * 100);},
										max_drift: 0, // the most a result could drift
										total_drift: 0,
										perfect_drift: 0,
										good_drift: 0,
										good_drift_possible: 0,
										over_flops: 0,
										under_flops: 0,
										bad_drift: 0,
										bad_drift_possible: 0,
										neutral_drift: 0,
										neutral_drift_good: 0,
										neutral_drift_bad: 0,
										good_drift_ratio: function(){return Math.round(this.good_drift / this.good_drift_possible * 100);},
										bad_drift_ratio: function(){return Math.round(this.bad_drift / this.bad_drift_possible * 100);}, 
										perfect_drift_percent: function(){return Math.round(this.perfect_drift / this.total * 100);}, 
										good_drift_percent: function(){return Math.round(this.good_drift / this.total * 100);}, 
										bad_drift_percent: function(){return Math.round(this.bad_drift / this.total * 100);}, 
										over_flop_percent: function(){return Math.round(this.over_flops / this.total * 100);}, 
										under_flop_percent: function(){return Math.round(this.under_flops / this.total * 100);}, 
										neutral_drift_percent: function(){return Math.round(this.neutral_drift / this.total * 100);},
										neutral_drift_good_percent: function(){return Math.round(this.neutral_drift_good / this.total * 100);},
										neutral_drift_bad_percent: function(){return Math.round(this.neutral_drift_bad / this.total * 100);}
									};
								}
								
								treatments[treatment].total += 1;
								treatments[treatment].max_distance += Math.max(Math.abs(real - 5), Math.abs(real - 1));
								treatments[treatment].pre_total_distance += Math.abs(pre - real);
								treatments[treatment].pre_over_count += (pre > real)?1:0;
								treatments[treatment].pre_over_max += (real < 5)?1:0;
								treatments[treatment].pre_under_count += (pre < real)?1:0;
								treatments[treatment].pre_under_max += (real > 1)?1:0;
								treatments[treatment].pre_exact_count += (pre == real)?1:0;
								if(pre > real) {
									treatments[treatment].pre_over_distance += pre - real;
									treatments[treatment].pre_over_max_distance += 5 - real;
								}
								if(pre < real) {
									treatments[treatment].pre_under_distance += real - pre;
									treatments[treatment].pre_under_max_distance += real - 1;
								}
								treatments[treatment].post_total_distance += Math.abs(post - real);
								treatments[treatment].post_over_count += (post > real)?1:0;
								treatments[treatment].post_over_max += (real < 5)?1:0;
								treatments[treatment].post_under_count += (post < real)?1:0;
								treatments[treatment].post_under_max += (real > 1)?1:0;
								treatments[treatment].post_exact_count += (post == real)?1:0;
								if(post > real) {
									treatments[treatment].post_over_distance += post - real;
									treatments[treatment].post_over_max_distance += 5 - real;
								}
								if(post < real) {
									treatments[treatment].post_under_distance += real - post;
									treatments[treatment].post_under_max_distance += real - 1;
								}
								treatments[treatment].max_drift += Math.max(Math.abs(pre - 5), Math.abs(pre - 1));
								treatments[treatment].total_drift += Math.abs(pre - post);
								treatments[treatment].perfect_drift += (pre != real && post == real)?1:0;
								treatments[treatment].good_drift += ((Math.abs(pre-real) > Math.abs(post-real)) && ((pre < real && pre < post) || (pre > real && pre > post)))?1:0;
								treatments[treatment].over_flops += ((Math.abs(pre-real) <= Math.abs(post-real)) && (pre < real && pre < post))?1:0;
								treatments[treatment].under_flops += ((Math.abs(pre-real) <= Math.abs(post-real)) && (pre > real && pre > post))?1:0;
								treatments[treatment].good_drift_possible += (pre != real )?1:0;
								treatments[treatment].bad_drift += ((pre <= real && pre > post) || (pre >= real && pre < post))?1:0;
								treatments[treatment].bad_drift_possible += (pre == real || (pre < real && pre != 1) || (pre > real && pre != 5))?1:0;
								treatments[treatment].neutral_drift += (pre==post)?1:0;
								treatments[treatment].neutral_drift_good += ((pre==post)&&(pre==real))?1:0;
								treatments[treatment].neutral_drift_bad += ((pre==post)&&(pre!=real))?1:0;
								treatments[treatment].max_drift += Math.max(Math.abs(pre - 5), Math.abs(pre - 1));
							}
						}
						render();
					}
				});
			});
			
			function render() {
				$(".total_participants").text(phase_breakdown["total"]);
				$(".omitted_participants").text(phase_breakdown["omit"]);
				$(".pre_bail").text(phase_breakdown["pre"]);
				$(".mid_bail").text(phase_breakdown["mid"]);
				$(".post_bail").text(phase_breakdown["post"]);
				$(".complete").text(phase_breakdown["complete"]);
				$(".omit_percent").text(phase_breakdown.omit_percent());
				$(".pre_percent").text(phase_breakdown.pre_percent());
				$(".mid_percent").text(phase_breakdown.mid_percent());
				$(".post_percent").text(phase_breakdown.post_percent());
				$(".complete_percent").text(phase_breakdown.complete_percent());
				
				$(".strong_conservative").text(ideology["strong_conservative"]);
				$(".moderate_conservative").text(ideology["moderate_conservative"]);
				$(".independent").text(ideology["independent"]);
				$(".moderate_liberal").text(ideology["moderate_liberal"]);
				$(".strong_liberal").text(ideology["strong_liberal"]);
				$(".unspecified").text(ideology["unspecified"]);
				$(".strong_conservative_percent").text(ideology.strong_conservative_percent());
				$(".moderate_conservative_percent").text(ideology.moderate_conservative_percent());
				$(".independent_percent").text(ideology.independent_percent());
				$(".moderate_liberal_percent").text(ideology.moderate_liberal_percent());
				$(".strong_liberal_percent").text(ideology.strong_liberal_percent());
				$(".unspecified_percent").text(ideology.unspecified_percent());
				
				<?PHP
					$prefix_list = array("control","none","safe","goggles","highlight");
					foreach($prefix_list as $prefix) {
						?>
						$(".<?PHP echo($prefix); ?>_pre_distance").text(treatments["<?PHP echo($prefix); ?>"].pre_total_distance);
						$(".<?PHP echo($prefix); ?>_max_distance").text(treatments["<?PHP echo($prefix); ?>"].max_distance);
						$(".<?PHP echo($prefix); ?>_pre_inaccuracy_saturation").text(treatments["<?PHP echo($prefix); ?>"].pre_inaccuracy_saturation());
						$(".<?PHP echo($prefix); ?>_pre_over_bias_saturation").text(treatments["<?PHP echo($prefix); ?>"].pre_over_bias_saturation());
						$(".<?PHP echo($prefix); ?>_pre_under_bias_saturation").text(treatments["<?PHP echo($prefix); ?>"].pre_under_bias_saturation());
						$(".<?PHP echo($prefix); ?>_pre_over").text(treatments["<?PHP echo($prefix); ?>"].pre_over_count);
						$(".<?PHP echo($prefix); ?>_pre_over_max").text(treatments["<?PHP echo($prefix); ?>"].pre_over_max);
						$(".<?PHP echo($prefix); ?>_pre_over_distance").text(treatments["<?PHP echo($prefix); ?>"].pre_over_distance);
						$(".<?PHP echo($prefix); ?>_pre_over_max_distance").text(treatments["<?PHP echo($prefix); ?>"].pre_over_max_distance);
						$(".<?PHP echo($prefix); ?>_pre_over_saturation").text(treatments["<?PHP echo($prefix); ?>"].pre_over_saturation());
						$(".<?PHP echo($prefix); ?>_pre_under").text(treatments["<?PHP echo($prefix); ?>"].pre_under_count);
						$(".<?PHP echo($prefix); ?>_pre_under_max").text(treatments["<?PHP echo($prefix); ?>"].pre_under_max);
						$(".<?PHP echo($prefix); ?>_pre_under_distance").text(treatments["<?PHP echo($prefix); ?>"].pre_under_distance);
						$(".<?PHP echo($prefix); ?>_pre_under_max_distance").text(treatments["<?PHP echo($prefix); ?>"].pre_under_max_distance);
						$(".<?PHP echo($prefix); ?>_pre_under_saturation").text(treatments["<?PHP echo($prefix); ?>"].pre_under_saturation());
						$(".<?PHP echo($prefix); ?>_pre_exact").text(treatments["<?PHP echo($prefix); ?>"].pre_exact_count);
						$(".<?PHP echo($prefix); ?>_post_distance").text(treatments["<?PHP echo($prefix); ?>"].post_total_distance);
						$(".<?PHP echo($prefix); ?>_post_inaccuracy_saturation").text(treatments["<?PHP echo($prefix); ?>"].post_inaccuracy_saturation());
						$(".<?PHP echo($prefix); ?>_post_over_bias_saturation").text(treatments["<?PHP echo($prefix); ?>"].post_over_bias_saturation());
						$(".<?PHP echo($prefix); ?>_post_under_bias_saturation").text(treatments["<?PHP echo($prefix); ?>"].post_under_bias_saturation());
						$(".<?PHP echo($prefix); ?>_post_over").text(treatments["<?PHP echo($prefix); ?>"].post_over_count);
						$(".<?PHP echo($prefix); ?>_post_over_max").text(treatments["<?PHP echo($prefix); ?>"].post_over_max);
						$(".<?PHP echo($prefix); ?>_post_over_distance").text(treatments["<?PHP echo($prefix); ?>"].post_over_distance);
						$(".<?PHP echo($prefix); ?>_post_over_max_distance").text(treatments["<?PHP echo($prefix); ?>"].post_over_max_distance);
						$(".<?PHP echo($prefix); ?>_post_over_saturation").text(treatments["<?PHP echo($prefix); ?>"].post_over_saturation());
						$(".<?PHP echo($prefix); ?>_post_under").text(treatments["<?PHP echo($prefix); ?>"].post_under_count);
						$(".<?PHP echo($prefix); ?>_post_under_max").text(treatments["<?PHP echo($prefix); ?>"].post_under_max);
						$(".<?PHP echo($prefix); ?>_post_under_distance").text(treatments["<?PHP echo($prefix); ?>"].post_under_distance);
						$(".<?PHP echo($prefix); ?>_post_under_max_distance").text(treatments["<?PHP echo($prefix); ?>"].post_under_max_distance);
						$(".<?PHP echo($prefix); ?>_post_under_saturation").text(treatments["<?PHP echo($prefix); ?>"].post_under_saturation());
						$(".<?PHP echo($prefix); ?>_post_exact").text(treatments["<?PHP echo($prefix); ?>"].post_exact_count);
						$(".<?PHP echo($prefix); ?>_pre_over_percent").text(treatments["<?PHP echo($prefix); ?>"].pre_over_percent());
						$(".<?PHP echo($prefix); ?>_pre_under_percent").text(treatments["<?PHP echo($prefix); ?>"].pre_under_percent());
						$(".<?PHP echo($prefix); ?>_pre_exact_percent").text(treatments["<?PHP echo($prefix); ?>"].pre_exact_percent());
						$(".<?PHP echo($prefix); ?>_post_over_percent").text(treatments["<?PHP echo($prefix); ?>"].post_over_percent());
						$(".<?PHP echo($prefix); ?>_post_under_percent").text(treatments["<?PHP echo($prefix); ?>"].post_under_percent());
						$(".<?PHP echo($prefix); ?>_post_exact_percent").text(treatments["<?PHP echo($prefix); ?>"].post_exact_percent());
						$(".<?PHP echo($prefix); ?>_total").text(treatments["<?PHP echo($prefix); ?>"].total);
						$(".<?PHP echo($prefix); ?>_perfect_drift").text(treatments["<?PHP echo($prefix); ?>"].perfect_drift);
						$(".<?PHP echo($prefix); ?>_good_drift").text(treatments["<?PHP echo($prefix); ?>"].good_drift);
						$(".<?PHP echo($prefix); ?>_good_drift_possible").text(treatments["<?PHP echo($prefix); ?>"].good_drift_possible);
						$(".<?PHP echo($prefix); ?>_good_drift_ratio").text(treatments["<?PHP echo($prefix); ?>"].good_drift_ratio());
						$(".<?PHP echo($prefix); ?>_over_flops").text(treatments["<?PHP echo($prefix); ?>"].over_flops);
						$(".<?PHP echo($prefix); ?>_over_flop_percent").text(treatments["<?PHP echo($prefix); ?>"].over_flop_percent());
						$(".<?PHP echo($prefix); ?>_under_flops").text(treatments["<?PHP echo($prefix); ?>"].under_flops);
						$(".<?PHP echo($prefix); ?>_under_flop_percent").text(treatments["<?PHP echo($prefix); ?>"].under_flop_percent());
						$(".<?PHP echo($prefix); ?>_bad_drift").text(treatments["<?PHP echo($prefix); ?>"].bad_drift);
						$(".<?PHP echo($prefix); ?>_bad_drift_possible").text(treatments["<?PHP echo($prefix); ?>"].bad_drift_possible);
						$(".<?PHP echo($prefix); ?>_bad_drift_ratio").text(treatments["<?PHP echo($prefix); ?>"].bad_drift_ratio());
						$(".<?PHP echo($prefix); ?>_neutral_drift").text(treatments["<?PHP echo($prefix); ?>"].neutral_drift);
						$(".<?PHP echo($prefix); ?>_neutral_drift_good").text(treatments["<?PHP echo($prefix); ?>"].neutral_drift_good);
						$(".<?PHP echo($prefix); ?>_neutral_drift_bad").text(treatments["<?PHP echo($prefix); ?>"].neutral_drift_bad);
						$(".<?PHP echo($prefix); ?>_perfect_drift_percent").text(treatments["<?PHP echo($prefix); ?>"].perfect_drift_percent());
						$(".<?PHP echo($prefix); ?>_good_drift_percent").text(treatments["<?PHP echo($prefix); ?>"].good_drift_percent());
						$(".<?PHP echo($prefix); ?>_bad_drift_percent").text(treatments["<?PHP echo($prefix); ?>"].bad_drift_percent());
						$(".<?PHP echo($prefix); ?>_neutral_drift_percent").text(treatments["<?PHP echo($prefix); ?>"].neutral_drift_percent());
						$(".<?PHP echo($prefix); ?>_neutral_drift_good_percent").text(treatments["<?PHP echo($prefix); ?>"].neutral_drift_good_percent());
						$(".<?PHP echo($prefix); ?>_neutral_drift_bad_percent").text(treatments["<?PHP echo($prefix); ?>"].neutral_drift_bad_percent());
						<?PHP
					}
				?>
			}
			
		</script>
	</head>
	<body>
		<?PHP echo("<?PHP TESTING ?>");?>
		<div id="content">
			<h1>Results Summary</h1>
			<h2>Participation</h2>
			<h3>Completion</h3>
			<div class="results">
				<ul class="wide">
					<li><label>Total:</label><span class="total_participants"></span></li>
					<li><label>Omitted:</label><span class="omitted_participants"></span> (<span class="omit_percent"></span>%)</li>
					<li><label>Pre-Goggles:</label><span class="pre_bail"></span> (<span class="pre_percent"></span>%)</li>
					<li><label>Mid-Goggles:</label><span class="mid_bail"></span> (<span class="mid_percent"></span>%)</li>
					<li><label>Post-Goggles:</label><span class="post_bail"></span> (<span class="post_percent"></span>%)</li>
					<li><label>Complete:</label><span class="complete"></span> (<span class="complete_percent"></span>%)</li>
				</ul>
			</div>
			<h3>Demographics</h3>
			<h4>Political Leanings</h4>
			<div class="results">
				<ul class="wide">
					<li><label>Unspecified:</label><span class="unspecified"></span> (<span class="unspecified_percent"></span>%)</li>
					<li><label>Strong conservative:</label><span class="strong_conservative"></span> (<span class="strong_conservative_percent"></span>%)</li>
					<li><label>Moderate conservative:</label><span class="moderate_conservative"></span> (<span class="moderate_conservative_percent"></span>%)</li>
					<li><label>Independent:</label><span class="independent"></span> (<span class="independent_percent"></span>%)</li>
					<li><label>Moderate liberal:</label><span class="moderate_liberal"></span> (<span class="moderate_liberal_percent"></span>%)</li>
					<li><label>Strong liberal:</label><span class="strong_liberal"></span> (<span class="strong_liberal_percent"></span>%)</li>
				</ul>
			</div>
			<h2>Results</h2>
			<?PHP
				function results($prefix) {
					?>
			<div class="results">
				<h5>Pre-treatment</h5>
				<ul>
					<li>
						<label>Inaccuracy Saturation:</label>
						<span class="<?PHP echo($prefix); ?>_pre_distance" class="result"></span> / <span class="<?PHP echo($prefix); ?>_max_distance" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_pre_inaccuracy_saturation" class="result"></span>%)
					</li>
					<li class="useless">
						<label>Over:</label>
						<span class="<?PHP echo($prefix); ?>_pre_over" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_pre_over_percent" class="result"></span>%)
					</li>
					<li>
						<label>Over Saturation:</label>
						<span class="<?PHP echo($prefix); ?>_pre_over" class="result"></span> / <span class="<?PHP echo($prefix); ?>_pre_over_max" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_pre_over_saturation" class="result"></span>%)
					</li>
					<li>
						<label>Bias Saturation (Over):</label>
						<span class="<?PHP echo($prefix); ?>_pre_over_distance" class="result"></span> / <span class="<?PHP echo($prefix); ?>_pre_over_max_distance" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_pre_over_bias_saturation" class="result"></span>%)
					</li>
					<li class="useless">
						<label>Under:</label>
						<span class="<?PHP echo($prefix); ?>_pre_under" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_pre_under_percent" class="result"></span>%)
					</li>
					<li>
						<label>Under Saturation:</label>
						<span class="<?PHP echo($prefix); ?>_pre_under" class="result"></span> / <span class="<?PHP echo($prefix); ?>_pre_under_max" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_pre_under_saturation" class="result"></span>%)
					</li>
					<li>
						<label>Bias Saturation (Under):</label>
						<span class="<?PHP echo($prefix); ?>_pre_under_distance" class="result"></span> / <span class="<?PHP echo($prefix); ?>_pre_under_max_distance" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_pre_under_bias_saturation" class="result"></span>%)
					</li>
					<li>
						<label>Exact:</label>
						<span class="<?PHP echo($prefix); ?>_pre_exact" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_pre_exact_percent" class="result"></span>%)
					</li>
				</ul>
				<h5>Post-treatment</h5>
				<ul>
					<li>
						<label>Inaccuracy Saturation:</label>
						<span class="<?PHP echo($prefix); ?>_post_distance" class="result"></span> / <span class="<?PHP echo($prefix); ?>_max_distance" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_post_inaccuracy_saturation" class="result"></span>%)
					</li>
					<li class="useless">
						<label>Over:</label>
						<span class="<?PHP echo($prefix); ?>_post_over" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_post_over_percent" class="result"></span>%)
					</li>
					<li>
						<label>Over Saturation:</label>
						<span class="<?PHP echo($prefix); ?>_post_over" class="result"></span> / <span class="<?PHP echo($prefix); ?>_post_over_max" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_post_over_saturation" class="result"></span>%)
					</li>
					<li>
						<label>Bias Saturation (Over):</label>
						<span class="<?PHP echo($prefix); ?>_post_over_distance" class="result"></span> / <span class="<?PHP echo($prefix); ?>_post_over_max_distance" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_post_over_bias_saturation" class="result"></span>%)
					</li>
					<li class="useless">
						<label>Under:</label>
						<span class="<?PHP echo($prefix); ?>_post_under" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_post_under_percent" class="result"></span>%)
					</li>
					<li>
						<label>Under Saturation:</label>
						<span class="<?PHP echo($prefix); ?>_post_under" class="result"></span> / <span class="<?PHP echo($prefix); ?>_post_under_max" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_post_under_saturation" class="result"></span>%)
					</li>
					<li>
						<label>Bias Saturation (Under):</label>
						<span class="<?PHP echo($prefix); ?>_post_under_distance" class="result"></span> / <span class="<?PHP echo($prefix); ?>_post_under_max_distance" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_post_under_bias_saturation" class="result"></span>%)
					</li>
					<li>
						<label>Exact:</label>
						<span class="<?PHP echo($prefix); ?>_post_exact" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_post_exact_percent" class="result"></span>%)
					</li>
				</ul>
				<h5>Drift</h5>
				<ul>
					<li>
						<label>Perfect:</label>
						<span class="<?PHP echo($prefix); ?>_perfect_drift" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_perfect_drift_percent" class="result"></span>%)
					</li>
					<li class="useless">
						<label>Intended:</label>
						<span class="<?PHP echo($prefix); ?>_good_drift" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_good_drift_percent" class="result"></span>%)
					</li>
					<li>
						<label>Intended Saturation:</label>
						<span class="<?PHP echo($prefix); ?>_good_drift" class="result"></span> / <span class="<?PHP echo($prefix); ?>_good_drift_possible" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_good_drift_ratio" class="result"></span>%)
					</li>
					<li class="useless">
						<label>Backfire:</label>
						<span class="<?PHP echo($prefix); ?>_bad_drift" class="result"></span>/ <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_bad_drift_percent" class="result"></span>%)
					</li>
					<li>
						<label>Backfire Saturation:</label>
						<span class="<?PHP echo($prefix); ?>_bad_drift" class="result"></span> / <span class="<?PHP echo($prefix); ?>_bad_drift_possible" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_bad_drift_ratio" class="result"></span>%)
					</li>
					<li class="useless">
						<label>Neutral:</label>
						<span class="<?PHP echo($prefix); ?>_neutral_drift" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_neutral_drift_percent" class="result"></span>%)
					</li>
					<li>
						<label>Neutral (Exact):</label>
						<span class="<?PHP echo($prefix); ?>_neutral_drift_good" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_neutral_drift_good_percent" class="result"></span>%)
					</li>
					<li>
						<label>Neutral (Off):</label>
						<span class="<?PHP echo($prefix); ?>_neutral_drift_bad" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_neutral_drift_bad_percent" class="result"></span>%)
					</li>
					<li>
						<label>Positive Overcomp:</label>
						<span class="<?PHP echo($prefix); ?>_over_flops" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_over_flop_percent" class="result"></span>%)
					</li>
					<li>
						<label>Negative Overcomp:</label>
						<span class="<?PHP echo($prefix); ?>_under_flops" class="result"></span> / <span class="<?PHP echo($prefix); ?>_total" class="result"></span>
						(<span class="<?PHP echo($prefix); ?>_under_flop_percent" class="result"></span>%)
					</li>
				</ul>
			</div>
					<?PHP
				}
			
			?>
			<h3>Controls</h3>
			<h4>No Evidence</h4>
			<?PHP results("control"); ?>
				
			<h4>No Layer</h4>
			<?PHP results("none"); ?>
			
			<h3>Treatments</h3>
			<h4>Highlight Mode</h4>
			<?PHP results("highlight"); ?>
			
			<h4>Goggles Mode</h4>
			<?PHP results("goggles"); ?>
			
			<h4>Safe Mode</h4>
			<?PHP results("safe"); ?>
			
		</div>
	</body>
</html>