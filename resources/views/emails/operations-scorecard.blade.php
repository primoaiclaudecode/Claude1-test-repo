<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>SAM</title>

	<style>
		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 24px;
		}

		table th, table td {
			padding: 5px 10px;
			text-align: left;
		}

		table.bordered td {
			border: 1px solid #999;
		}

		.text-center {
			text-align: center !important;
		}
	</style>
</head>

<body>
	<h1>Operations Scorecard</h1>
	<table>
		<tbody>
			<tr>
				<td>
					<strong>Unit Name:</strong>
				</td>
				<td>{{ $unitInfo['unitName'] }}</td>
				<td>
					<strong>Date:</strong>
				</td>
				<td>{{ \Carbon\Carbon::parse($scoreCard->scorecard_date)->format('d-m-Y') }}</td>
			</tr>
			<tr>
				<td>
					<strong>Region:</strong>
				</td>
				<td>{{ $unitInfo['regionName'] }}</td>
				<td>
					<strong>Contract Status:</strong>
				</td>
				<td>{{ $unitInfo['contractStatus'] }}</td>
			</tr>
			<tr>
				<td>
					<strong>Operations manager:</strong>
				</td>
				<td>{{ $unitInfo['operationsManagerName'] }}</td>
				<td>
					<strong>Contract Type:</strong>
				</td>
				<td>{{ $unitInfo['contractType'] }}</td>
			</tr>
			<tr>
				<td>
					<strong>Client communications month to date:</strong>
				</td>
				<td>{{ $unitInfo['onsiteVisits'] }}</td>
				<td>
					<strong>Client contact:</strong>
				</td>
				<td>{{ $unitInfo['clientContact'] }}</td>
			</tr>
		</tbody>
	</table>
	<table class="bordered">
		<thead>
		<tr>
			<th width="250px">Performance metrics</th>
			<th width="150px" class="text-center">Score</th>
			<th>Notes</th>
		</tr>
		</thead>
		<tbody>
			@if (!$scoreCard->presentation_private)
				<tr>
					<td>Food offer/Presentation</td>
					<td class="text-center">{{ $scoreCard->presentation }}</td>
					<td>{{ $scoreCard->presentation_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->foodcost_awareness_private)
				<tr>
					<td>Food costings / account awareness</td>
					<td class="text-center">{{ $scoreCard->foodcost_awareness }}</td>
					<td>{{ $scoreCard->foodcost_awareness_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->hr_issues_private)
				<tr>
					<td>HR Issues</td>
					<td class="text-center">{{ $scoreCard->hr_issues }}</td>
					<td>{{ $scoreCard->hr_issues_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->morale_private)
				<tr>
					<td>Staff Morale</td>
					<td class="text-center">{{ $scoreCard->morale }}</td>
					<td>{{ $scoreCard->morale_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->purch_compliance_private)
				<tr>
					<td>Purchasing compliance</td>
					<td class="text-center">{{ $scoreCard->purch_compliance }}</td>
					<td>{{ $scoreCard->purch_compliance_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->haccp_compliance_private)
				<tr>
					<td>HACCP compliance</td>
					<td class="text-center">{{ $scoreCard->haccp_compliance }}</td>
					<td>{{ $scoreCard->haccp_compliance_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->health_safety_iso_private)
				<tr>
					<td>Health and Safety compliance</td>
					<td class="text-center">{{ $scoreCard->health_safety_iso }}</td>
					<td>{{ $scoreCard->health_safety_iso_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->accidents_incidents_private)
				<tr>
					<td>Accidents / Incidents</td>
					<td class="text-center">{{ $scoreCard->accidents_incidents }}</td>
					<td>{{ $scoreCard->accidents_incidents_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->security_cash_ctl_private)
				<tr>
					<td>Site security and cash control</td>
					<td class="text-center">{{ $scoreCard->security_cash_ctl }}</td>
					<td>{{ $scoreCard->security_cash_ctl_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->marketing_upselling_private)
				<tr>
					<td class="vertical-align-middle">Marketing / Upselling</td>
					<td class="text-center">{{ $scoreCard->marketing_upselling }}</td>
					<td>{{ $scoreCard->marketing_upselling_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->training_private)
				<tr>
					<td class="vertical-align-middle">Training</td>
					<td class="text-center">{{ $scoreCard->training }}</td>
					<td>{{ $scoreCard->training_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->objectives_private)
				<tr>
					<td class="vertical-align-middle">Objectives (Month)</td>
					<td class="text-center">{{ $scoreCard->objectives }}</td>
					<td>{{ $scoreCard->objectives_notes }}</td>
				</tr>
			@endif
			@if (!$scoreCard->outstanding_issues_private)
				<tr>
					<td class="vertical-align-middle">Issues outstanding</td>
					<td></td>
					<td>{{ $scoreCard->outstanding_issues }}</td>
				</tr>
			@endif
			@if (!$scoreCard->sp_projects_functions_private)
				<tr>
					<td class="vertical-align-middle">Special projects/functions</td>
					<td></td>
					<td>{{ $scoreCard->sp_projects_functions }}</td>
				</tr>
			@endif
			@if (!$scoreCard->innovation_private)
				<tr>
					<td class="vertical-align-middle">Innovation/Chef's WhatsApp Group</td>
					<td></td>
					<td>{{ $scoreCard->innovation }}</td>
				</tr>
			@endif
			@if (!$scoreCard->add_support_req_private)
				<tr>
					<td class="vertical-align-middle">Additional Support required</td>
					<td></td>
					<td>{{ $scoreCard->add_support_req }}</td>
				</tr>
			@endif
		</tbody>
	</table>
</body>
</html>
