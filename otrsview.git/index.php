<html>
	<head>
		<title>OTRS Ticket Viewer</title>
		<link href="https://fonts.googleapis.com/css?family=Karla" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<meta http-equiv="refresh" content="60">
	</head>

	<body>
	
		<div id="title" class="pagehead">Engineering Tickets</div>

		<div id="tickets">
		<table style="width:100%;">
			<tr style="background-color: #DDDDDD;">
				<!-- <td><div class="tickethead">Priority</div></td> -->
				<td><div class="tickethead">Title</div></td>
				<td><div class="tickethead">Customer</div></td>
				<td><div class="tickethead">Owner</div></td>
				<td><div class="tickethead">Created</div></td>
				<td><div class="tickethead">Age</div></td>
			</tr>
				
		<?php
		
		function secondsToTime($inputSeconds) {

			$secondsInAMinute = 60;
			$secondsInAnHour  = 60 * $secondsInAMinute;
			$secondsInADay    = 24 * $secondsInAnHour;

			// extract days
			$days = floor($inputSeconds / $secondsInADay);

			// extract hours
			$hourSeconds = $inputSeconds % $secondsInADay;
			$hours = floor($hourSeconds / $secondsInAnHour);

			// extract minutes
			$minuteSeconds = $hourSeconds % $secondsInAnHour;
			$minutes = floor($minuteSeconds / $secondsInAMinute);

			// extract the remaining seconds
			$remainingSeconds = $minuteSeconds % $secondsInAMinute;
			$seconds = ceil($remainingSeconds);

			// return the final array
			$obj = array(
				'd' => (int) $days,
				'h' => (int) $hours,
				'm' => (int) $minutes,
				's' => (int) $seconds,
			);
			return $obj;
		}
		
		require_once 'HTTP/Request2.php';
		
		//
		// Get list of all tickets in Engineering queue
		//

		$request = new Http_Request2('http://your.otrs.url/otrs/nph-genericinterface.pl/Webservice/GenericTicketConnector/Ticket'); // OTRS URL
		$url = $request->getUrl();
		
		$headers = array(
			'Content-type' => 'application/json',
		);
		
		$request->setHeader($headers);

		$parameters = array(
			'UserLogin' => '', // User Name of an agent
			'Password' => '', // Password
			'Queues' => '', // Queues to display
		);

		$url->setQueryVariables($parameters);

		$request->setMethod(HTTP_Request2::METHOD_GET);

		try
		{
			$response = $request->send();
			$ticket_data = $response->getBody();
		}
		catch (HttpException $ex)
		{
			echo $ex;
		}
		
		$all_tickets = json_decode($ticket_data, true);
		
		//
		// Get data for all_tickets into an array
		// 
		
		$tstr = "";
		foreach ($all_tickets['TicketID'] as $tkey => $tval) {
			$tstr = $tstr . $all_tickets['TicketID'][$tkey] . ",";
		}
		
		$tfoo = "http://your.otrs.url/otrs/nph-genericinterface.pl/Webservice/GenericTicketConnector/Ticket/" . $tstr; // OTRS URL
		
		$request = new Http_Request2($tfoo);
		$url = $request->getUrl();
		
		$headers = array(
			'Content-type' => 'application/json',
		);
		
		$request->setHeader($headers);

		$parameters = array(
			'UserLogin' => '', // User name of an agent
			'Password' => '', // Password
		);

		$url->setQueryVariables($parameters);

		$request->setMethod(HTTP_Request2::METHOD_GET);
		
		try
		{
			$response = $request->send();
			$ticket_list = $response->getBody();
		}
		catch (HttpException $ex)
		{
			echo $ex;
		}
		
		$list_tickets = json_decode($ticket_list, true);
		
		//
		// Colour codding for row backgrounds
		//
		
		$ticket_priority = array(
			"5 very high"=>"FFAAAA",
			"4 high"=>"FFFFAA",
			"3 normal"=>"FFFFFF",
			"2 low"=>"AAFFFF",
			"1 very low"=>"AAAAFF",
		);
		
		//
		// Build the table
		//
		
		foreach ($list_tickets['Ticket'] as $this_ticket) {		
			if ($this_ticket['State'] == "new") {	
				echo '<tr style="background-color: #' . $ticket_priority[$this_ticket['Priority']] . ';">';
					// echo '<td><div class="ticketcell">' . ucfirst(substr($this_ticket['Priority'],2)) . '</div></td>';
					echo '<td><div class="ticketcell">' . $this_ticket['Title'] . '</div></td>';
					echo '<td><div class="ticketcell">' . $this_ticket['CustomerUserID'] . '</div></td>';
					echo '<td><div class="ticketcell">';
					if ($this_ticket['Owner'] == "root@localhost") {
						echo '<div style="font-size:0.75em;color:#FF0000;">Unassigned</div>';
					} else {
						echo $this_ticket['Owner'];
					}
					echo '</div></td>';
					echo '<td><div class="ticketcell">' . date('d M',strtotime($this_ticket['Created'])) . '</div></td>';
					echo '<td><div class="ticketcell">';
					if ($this_ticket['Age'] <= '86400') {
						echo secondsToTime($this_ticket['Age'])['h'] . 'h ';
						echo secondsToTime($this_ticket['Age'])['m'] . 'm' ;
					} else {
						echo secondsToTime($this_ticket['Age'])['d'] . 'd ';
						echo secondsToTime($this_ticket['Age'])['h'] . 'h ';
					}							
					echo '</div></td>';
				echo '</tr>'; 
			}	
		}
		
		?>
		
		</table>
		</div>
	</body>
</html>