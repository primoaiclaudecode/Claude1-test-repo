@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">Catalog Details</header>
		<table class="table table-striped table-advance table-hover">
			<tr>
				<th>Title</th>
				<td>{!! $catalog->catalog_title !!}</td>
			</tr>
			<tr>
				<th>Status</th>
				<td>{!! $catalog->myStatus() !!}</td>
			</tr>
		</table>
	</section>
	@if(isset($catalogItems[0]) && is_array($catalogItems[0]))
		@foreach($catalogItems[0] as $catalogCatItemId => $itemCatDetail)
			<section class="panel">
				<header class="panel-heading">{!! $itemCatDetail->item_title !!}</header>
				<div class="panel-body">
					<section id="no-more-tables">
						<table class="table table-bordered table-striped table-condensed cf">
							<thead class="cf">
								<tr>
									<th>Item Title</th>
									<th>Description</th>
									<th>Price</th>
								</tr>
							</thead>
							<tbody>
								@if(isset($catalogItems[$itemCatDetail->client_catalog_item_id]))
									@foreach($catalogItems[$itemCatDetail->client_catalog_item_id] as $itemId => $itemDetail)
										<tr>
											<td data-title="Item Title">{!! $itemDetail->item_title !!}&nbsp;</td>
											<td data-title="Description">{!! $itemDetail->item_description !!}&nbsp; </td>
											<td data-title="Price">{!! $itemDetail->item_price !!} &nbsp;</td>
										</tr>
									@endforeach
								@endif
							</tbody>
						</table>
					</section>
				</div>
			</section>
		@endforeach
	@endif
@stop