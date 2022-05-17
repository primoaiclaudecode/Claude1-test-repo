<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>SAM</title>
</head>

<body>
<h4>Problem Report summary</h4>

@foreach($emailData as $item)
	<table style="margin-bottom: 20px">
		<tr>
			<td><b>CAR#:</b></td>
			<td><a href="{{ action('SheetController@problemReport') }}/{{ $item->id }}">{{ $item->id }}</a></td>
		</tr>
		<tr>
			<td><b>Date report opened:</b></td>
			<td>{{ $item->problemDate }}</td>
		</tr>
		<tr>
			<td><b>How long has report been opened:</b></td>
			<td>{{ $item->problemDuration }}</td>
		</tr>
		<tr>
			<td><b>User:</b></td>
			<td>{{ $item->userName }}</td>
		</tr>
		<tr>
			<td><b>Unit:</b></td>
			<td>{{ $item->unitName }}</td>
		</tr>
		<tr>
			<td><b>Problem type:</b></td>
			<td>{{ $item->problemName }}</td>
		</tr>
	</table>
@endforeach

</body>
</html>
