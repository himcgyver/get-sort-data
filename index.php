<?php

# # # # # # # # # # # # # # # # # #
# Set URL variables.							#
# # # # # # # # # # # # # # # # # #
define("FIXTURE_URL", "http://82.135.146.98/test-services/scheduleAndResults.php");
define("TEAMS_URL", "http://82.135.146.98/test-services/team.php?id=");
define("RETRY_COUNT", 5);

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
# Function to establish cURL session with the services and return data.	#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
function curlSession($url) {
	$error_msg = null;
	$session = curl_init();
	curl_setopt_array($session, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $url,
		CURLOPT_FAILONERROR => true
	]);
	$results = curl_exec($session);

	# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
	# If curl_exec failed, it will call a function to re-try x times,	#
	# (set in RETRY_COUNT) before returning an error.									#
	# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #	#
	if (curl_getinfo($session)['http_code'] >= 400) {
		$results = retryExecution(RETRY_COUNT, $session);
	}
	$error_msg = curl_error($session);
	curl_close($session);
	if ($error_msg !== '' && $results === false) {
		errorHandling($error_msg);
	} else {
		return json_decode($results);
	}
}

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#	Function to re-try x times (defined in "RETRY COUNT") before 	#
# returning an error.																						#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
function retryExecution($retry, $session) {
	$retry = 0;
	while ($retry < RETRY_COUNT) {
		$results = curl_exec($session);
		if (curl_getinfo($session)['http_code'] < 400) {
			return $results;
		}
		usleep(100000);
		$retry++;
	}
	return false;
}

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
# Function for getting information about the team by it's 'id'.	#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
function getTeamname($id) {
	$team_url = TEAMS_URL . $id;
	return curlSession($team_url);
}

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
# Function for Error Handling.														#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
function errorHandling($error) {
	echo "<b>Error:</b> $error ";
  echo "<br />";
}

$fixture_results = curlSession(FIXTURE_URL);

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #	# # # # # #
# 1. Create an empty array which will later hold below key, value pairs:		#
# 	 teamID => (team_name, win_count).																			#
# 2. Extract fixtures info from URL and if 'id' key doesn't exist in array:	#
#		 2.1 For key ('id') set values ('team name', 'initial win count = 0').	#
# 3. Compare teamA and teamB scores and increase win count for the winner,	#
#		 otherwise, (no winner/draw) continue without adding win to any team.		#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
$teams_array = array();
foreach ($fixture_results as $fixture_result) {
	$teamA = $fixture_result->teamAId;
	$teamB = $fixture_result->teamBId;
	$scoreA = $fixture_result->scoreA;
	$scoreB = $fixture_result->scoreB;
	if (!array_key_exists($teamA, $teams_array)) { #DRY!!
		$team_info = getTeamname($teamA);
		$team_name = $team_info->name;
		$teams_array[$teamA] = array($team_name, 0);
	}
	if (!array_key_exists($teamB, $teams_array)) { #DRY!!
		$team_info = getTeamname($teamB);
		$team_name = $team_info->name;
		$teams_array[$teamB] = array($team_name, 0);
	}
	if ($scoreA > $scoreB) {
		$teams_array[$teamA][1] += 1;
	} elseif ($scoreB > $scoreA) {
		$teams_array[$teamB][1] += 1;
	} else {
		continue;
	}
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="utf-8">
		<title>MyScore</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
		<link href="https://fonts.googleapis.com/css?family=Assistant|Fira+Sans:800|Montserrat|Ubuntu"  rel="stylesheet">
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="static/css/styles.css">
		<script type="text/javascript" src="static/js/sort.js"></script>

	</head>
	<body>
		<div class="container shadow mb-5 bg-white rounded">
			<div class="grid-item-1">
				<nav class="navbar navbar-light bg-light" >
				  <a class="navbar-brand" href="#">Welcome, home!</a>
				</nav>
			</div>
			<div class="grid-item-6">
				<hr class="line shadow">
			</div>
			<div class="grid-item-2 shadow mb-3 rounded">
					<table class="table table-sm table-striped table-dark" id="fixtureTable">
						<thead>
							<tr>
								<th scope="col"><a><i class="fas fa-sort"></i> Date</a></th>
								<th scope="col"><a><i class="fas fa-sort"></i> Team A</a></th>
								<th scope="col"><a><i class="fas fa-sort"></i> Team B</a></th>
								<th scope="col"><a><i class="fas fa-sort"></i> Score</a></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach($fixture_results as $fixture_result) { ?>
								<tr>
									<td><?php echo $fixture_result->date ?></td>
									<td><?php echo $teams_array[$fixture_result->teamAId][0] ?></td>
									<td><?php echo $teams_array[$fixture_result->teamBId][0] ?></td>
									<?php if($fixture_result->scoreA || $fixture_result->scoreB) { ?>
										<td><?php echo $fixture_result->scoreA . ' : ' . $fixture_result->scoreB ?></td>
									<?php } else { ?>
										<td> </td>
									<?php } ?>
								</tr>
						<?php } ?>
						</tbody>
					</table>
			</div>
			<div class="grid-item-3 shadow mb-5 rounded"> <!-- #3 item START -->
				<table class="table table-sm table-striped table-dark" id="victoryTable">
					<thead>
						<tr>
							<th scope="col"><a><i class="fas fa-sort"></i> Name</a></th>
							<th scope="col"><a><i class="fas fa-sort"></i> Wins</a></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($teams_array as $team_array) { ?>
							<tr>
								<td><?php echo $team_array[0] ?></td>
								<td><?php echo $team_array[1] ?></td>
							</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="grid-item-4">
				<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
			</div>
			<div class="grid-item-5" id="contacts">
				<hr class="line shadow">
				<footer class="white-section">
					<div class="container-fluid">
						<i class="social-icon fab fa-facebook-f"></i>
						<i class="social-icon fab fa-twitter"></i>
						<i class="social-icon fab fa-instagram"></i>
						<i class="social-icon fas fa-envelope"></i>
						<p>© Copyright 2019</p>
					</div>
				</footer>
			<div>
		</div>
	</body>
</html>
