@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Directories / Files</strong></div>
			</div>
		</header>

		<div class="panel-body">
			@if(Session::has('flash_message'))
			    <div class="alert alert-success"><em> {{ session('flash_message') }}</em></div>
			@endif

			@if(count($errors) > 0)
				<div class="alert alert-danger">
					<em>
						<ul>
							@foreach($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</em>
				</div>
			@endif

		<!-- Bread Crumb -->
		<ol class="breadcrumb breadcrumb-font">
		  {!! $breadCrumbs !!}
		</ol>
		<!-- Bread Crumb -->
                
        <div class="responsive-content">
			<div style="min-width: 750px">
				{!! $directories !!}
				{!! $files !!}
			</div>
		</div>

		@if($writePermissions)
			<div class="btn-toolbar margin-top-25">
	            <input type='submit' id="btn_upload_file" class="btn btn-primary btn-md" name='submit' value='Upload File' />
	            <input type='submit' id="btn_create_folder" class="btn btn-primary btn-md" name='submit' value='Create Directory' />
	        </div>
        @endif

        <!-- Upload File Modal -->
		<div id="upload_file" class="modal fade">
		    {!! Form::open(['url' => '/files/upload-file', 'class' => 'form-horizontal form-bordered', 'files' => true]) !!}
		    {{ Form::hidden('current_dir_path', $currentDirPath) }}
		    {{ Form::hidden('dir_id', $dirId) }}
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="panel-title">File Upload</h4>
		            </div>
		            <div class="modal-body">
		                <div class="form-group margin-bottom-0">
		                    <label class="col-md-2 control-label modal-label" for="name">Browse: </label>
		                    <div class="col-md-10">
		                        <input type='file' name="file_name" id="name" tabindex="2" class="form-control" />
		                    </div>
		                </div>
		            </div>
		            <div class="modal-footer">
		                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		                <button type="submit" name="submit" value="Upload File" class="btn btn-primary" id="upload_file_btn">Upload File</button>
		            </div>
		        </div>
		    </div>
		    </form>
		</div>
		<!-- Upload File Modal -->

		<!-- Create Folder Modal -->
		<div id="create_folder" class="modal fade">
		    {!! Form::open(['url' =>  '/files/create-dir', 'class' => 'form-horizontal form-bordered', 'id'=>'create_folder_form']) !!}
		    {{ Form::hidden('current_dir_path', $currentDirPath) }}
		    {{ Form::hidden('dir_id', $dirId) }}
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="panel-title">Create Directory</h4>
		            </div>
		            <div class="modal-body">
		                <div class="form-group margin-bottom-0">
		                    <label class="col-md-2 control-label modal-label" for="name">Name: </label>
		                    <div class="col-md-10">
		                        <input type='text' name="folder_name" value="" id="folder_name" class='form-control' tabindex="1" />
		                    </div>
		                </div>
		            </div>
		            <div class="modal-footer">
		                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		                <button type="submit" name="submit" value="Create Directory" id="create_folder_btn" class="btn btn-primary" >Create Directory</button>
		            </div>
		        </div>
		    </div>
		    {!!Form::close()!!}
		</div>
		<!-- Create Folder Modal -->

		<!-- Open / Save Modal -->
		<div id="open_save" class="modal fade">
		    {!! Form::open(['url' =>  '/files/open-download-file', 'class' => 'form-horizontal form-bordered', 'id' => 'form_open_save']) !!}
		    {{ Form::hidden('dir_id', $dirId) }}
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="panel-title">Open / Download</h4>
		            </div>
		            <div class="modal-body">
		                <div>
		                    If you want to open the file, please click the Open button and if you want to download the file, please click the Download button.
		                </div>
		            </div>
		            <div class="modal-footer">
		            	<!-- <input type="hidden" name="hidden_file_id" value=""> -->
		                <button type="submit" name="submit_open" value="Open" class="btn btn-primary">Open</button>
		                <button id="download_btn" type="submit" name="submit_download" value="Download" class="btn btn-primary">Download</button>
		            </div>
		        </div>
		    </div>
		    </form>
		</div>
		<!-- Open / Save Modal -->

		<!-- File/Folder Move Modal -->
		<div id="file_move" class="modal fade">
		    {!! Form::open(['url' => '/files/move-file', 'class' => 'form-horizontal form-bordered', 'id' => 'form_move_file']) !!}
		    {{ Form::hidden('current_dir_path', $currentDirPath) }}
		    {{ Form::hidden('dir_id', $dirId) }}
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="panel-title dynamic-title">Move File</h4>
		            </div>
		            <div class="modal-body">
		                <div class="form-group">
		                    <label class="col-md-2 control-label modal-label" for="folder_to_move">Folder: </label>
		                    <div class="col-md-10">
		                    	<select name="folder_to_move" id="folder_to_move" class="form-control" tabindex="1">
		                    		<option value="-1">Select Folder</option>
		                    		<option value="0">/</option>
		                    		<?php foreach($folderList as $fl) { ?>
										<option value="<?php echo $fl["id"] ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $fl["dir_file_name"]; ?></option>
									<?php } ?>
		                    	</select>
		                    </div>
		                </div>
		            </div>
		            <div class="modal-footer">
		                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		                <button type="submit" name="submit" value="Move File" class="btn btn-primary btn-title move_file_btn">Move File</button>
		            </div>
		        </div>
		    </div>
		    </form>
		</div>
		<!-- File/Folder Move Modal -->

		<!-- Folder Permissions Modal -->
		<div id="permissions" class="modal fade permission-modal">
		    {!! Form::open(['url' => '/files/set-directory-permission', 'class' => 'form-horizontal form-bordered', 'id' => 'form_permissions']) !!}
		    {{ Form::hidden('dir_id', $dirId) }}
		    {{ Form::hidden('type', $d_type) }}
		    <div class="modal-dialog">
		        <div class="modal-content dir-permission-modal-height">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="panel-title" id="folder_title">Fetching...</h4>
		            </div>
		            <div class="modal-body">
		                <div class="form-group">
		                    <div class="col-md-3 control-label img-label padding-top-0 permissions-model-label" for="name"><img src="<?php echo $appUrl; ?>img/dir.jpg"> </div>
		                    <div class="col-md-9 folder-label" id="folders_files_count">
		                        Fetching...
		                    </div>
		                </div>
		                <div class="form-group">
		                    <div>
			                    <div class="col-md-3 padding-top-0 permissions-model-label" for="folder_to_move">Location: </div>
			                    <div class="col-md-9" id="folder_location">
			                    	Fetching...
			                    </div>
		                    </div>
		                    <div>
			                    <div class="col-md-3 padding-top-0 permissions-model-label" for="folder_to_move">Size: </div>
			                    <div class="col-md-9" id="folder_size">
			                    	Calculating...
			                    </div>
		                    </div>
		                    <div>
			                    <div class="col-md-3 padding-top-0 permissions-model-label" for="folder_to_move">Last Modified: </div>
			                    <div class="col-md-9" id="folder_last_modified">
			                    	Fetching...
			                    </div>
		                    </div>
		                </div>
		                <div class="">
						    <div class="row user_div">
						        <div class="col-md-12 permissions-model-label padding-top-0 font-weight-bold margin-bottom-5">Permissions: </div>
						    </div>
						</div>
		                <div class="form-group permissions-section" id="user_str">
		                    Fetching...
		                </div>
		                <div class="">
		                	<div class="row">
				                <div class="col-md-6">
				                	&nbsp;
				                </div>
				                <div class="col-md-6">
				                	<input type="checkbox" name="recursive_permissions" id="recursive_permissions" value="1" class="margin-top-7">
				                    <label for="recursive_permissions" class="label-cursor">Apply permissions to all sub-folders</label>
				                </div>
		                	</div>
		            	</div>
		            </div>
		            <!--old-->
		            <div class="modal-footer">
		            	<div class="col-md-5 margin-left-minus-80">
	                		<button type="button" name="add_user_group" id="add_user_group" value="Add User Group" class="btn btn-primary btn-title  dir_add_user_group">Add User / Group</button>
		            	</div>
			            <div class="col-md-7 col-md-7-padding-right margin-left-80">
				            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			                <button type="submit" name="submit" value="Set Permission" class="btn btn-primary btn-title dir_set_permission">Set Permission</button>
			            </div>
		            </div>
		            <!--old-->
		        </div>
		    </div>
		    </form>
		</div>
		<!-- Folder Permissions Modal -->

		<!-- File Permissions Modal -->
		<div id="file_permissions" class="modal fade permission-modal">
		    {!! Form::open(['url' => '/files/set-file-permission', 'class' => 'form-horizontal form-bordered', 'id' => 'form_permissions_file']) !!}
		    {{ Form::hidden('dir_id', $dirId) }}
		    {{ Form::hidden('type', $d_type) }}
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="panel-title" id="file_title">Fetching...</h4>
		            </div>
		            <div class="modal-body">
		                <div class="form-group">
		                    <div>
			                    <div class="col-md-3 padding-top-0 permissions-model-label" for="folder_to_move">Location: </div>
			                    <div class="col-md-9" id="file_location">
			                    	Fetching...
			                    </div>
		                    </div>
		                    <div>
			                    <div class="col-md-3 padding-top-0 permissions-model-label" for="folder_to_move">Size: </div>
			                    <div class="col-md-9" id="file_size">
			                    	Calculating...
			                    </div>
		                    </div>
		                    <div>
			                    <div class="col-md-3 padding-top-0 permissions-model-label" for="folder_to_move">Last Modified: </div>
			                    <div class="col-md-9" id="file_last_modified">
			                    	Fetching...
			                    </div>
		                    </div>
		                </div>
		                <div class="">
		                    <div class="row file_user_div">
						        <div class="col-md-12 permissions-model-label padding-top-0 font-weight-bold margin-bottom-5">Permissions: </div>
					        </div>
		                </div>
		                <div class="form-group permissions-section" id="file_user_str">
		                    Fetching...
		                </div>
		            </div>
		            <div class="modal-footer">
		            	<div class="col-md-5 margin-left-minus-80">
		            		<a data-toggle="modal" href="#file_add_user_group_div" name="file_add_user_group" id="file_add_user_group" value="Add User Group" class="btn btn-primary btn-title file-add-user-group dir_add_user_group">Add User / Group</a>
		            	</div>
		                <div class="col-md-7 col-md-7-padding-right margin-left-80">
			                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			                <button type="submit" name="file_submit" value="Set Permission" class="btn btn-primary btn-title dir_set_permission">Set Permission</button>
		                </div>
		            </div>
		        </div>
		    </div>
		    </form>
		</div>
		<!-- File Permissions Modal -->

		<!-- Add User / Group Modal -->
		<div id="add_user_group_div" class="modal fade">
		    {!! Form::open(['url' => '/files/add-user-group', 'class' => 'form-horizontal form-bordered', 'id' => 'form_add_user_group']) !!}
		    {{ Form::hidden('dir_id', $dirId) }}
		    {{ Form::hidden('type', $d_type) }}
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close close_add_user_group_div" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="panel-title">Add User / Group to: <span id="add_user_group_folder_location">Fetching...</span></h4>
		            </div>
		            <div class="modal-body">
		                <div class="form-group">
		                    <label class="col-md-2 control-label permissions-model-label" for="folder_to_move">Folder: </label>
		                    <div class="col-md-10">
		                    	<input type='text' name="add_user_group_folder_name" value="Fetching..." id="add_user_group_folder_name" class='form-control' readonly="" />
		                    </div>
		                </div>
		                <div class="form-group">
		                    <label class="col-md-2 control-label permissions-model-label" for="folder_to_move">Groups: </label>
		                    <div class="col-md-10">
			                	@foreach($user_groups as $user_group) 
									<span style="display:none" id="user_group_selectbox_groupname_{{ $user_group->user_group_id }}">{{ $user_group->user_group_name }}</span>
								@endforeach
								<select name="user_groups_selectbox[]" id="user_groups_selectbox" class="form-control" tabindex="1" multiple = "multiple">
			                		@foreach($user_groups as $user_group) 
										<option value="{{ $user_group->user_group_id }}">{{ $user_group->user_group_name }}</option>
									@endforeach
		                    	</select>
		                    </div>
		                </div>

		                <div class="form-group">
                         <label class="col-md-2 control-label permissions-model-label" for="folder_to_move">Region: </label>
		                    <div class="col-md-10">
			                	@foreach($region_groups as $region_group) 
									<span style="display:none" id="region_group_selectbox_groupname_{{ $region_group->region_id }}">{{ $region_group->region_name }}</span>
								@endforeach
								<select name="region_groups_selectbox[]" id="region_groups_selectbox" class="form-control" tabindex="1" multiple = "multiple">
			                		@foreach($region_groups as $region_group) 
										<option value="{{ $region_group->region_id }}">{{ $region_group->region_name }}</option>
									@endforeach
		                    	</select>
		                    </div>
		                </div>                      
                      
		                <div class="form-group">
		                    <label class="col-md-2 control-label permissions-model-label" for="folder_to_move">Users: </label>
		                    <div class="col-md-10">
		                    		@foreach($users_data as $user_data)
										<span style="display:none" id="user_selectbox_username_{{ $user_data->user_id }}">{{ $user_data->username }}</span>
									@endforeach
		                    	<select name="user_selectbox[]" id="user_selectbox" class="form-control" tabindex="2" multiple = "multiple">
		                    		@foreach($users_data as $user_data)
		                    			<option value="{{ $user_data->user_id }}">{{ $user_data->username }}</option>
									@endforeach
		                    	</select>
		                    </div>
		                </div>
		            </div>
		            <div class="modal-footer">
		            	<div class="col-md-6" id="user_group_message"></div>
		                <div class="col-md-6">
			                <button type="button" class="btn btn-default close_add_user_group_div" data-dismiss="modal">Close</button>
			                <button type="button" name="add_user_group_btn" id="add_user_group_btn" value="Add User Group" class="btn btn-primary btn-title">Add User / Group</button>
		                </div>
		            </div>
		        </div>
		    </div>
		    </form>
		</div>
		<!-- Add User / Group Modal -->

		<!-- File Add User / Group Modal -->
		<div id="file_add_user_group_div" class="modal fade">
		    {!! Form::open(['url' => '/files/add-file-user-group', 'class' => 'form-horizontal form-bordered', 'id' => 'file_form_add_user_group']) !!}
		    {{ Form::hidden('dir_id', $dirId) }}
		    {{ Form::hidden('type', $d_type) }}
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close close_file_add_user_group_div" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="panel-title">Add User / Group to: <span id="add_user_group_file_title">Fetching...</span></h4>
		            </div>
		            <div class="modal-body">
		                <div class="form-group">
		                    <label class="col-md-2 control-label permissions-model-label" for="folder_to_move">File: </label>
		                    <div class="col-md-10">
		                    	<input type='text' name="add_user_group_folder_name" value="Fetching..." id="add_user_group_file_name" class='form-control' readonly="" />
		                    </div>
		                </div>
		                <div class="form-group">
		                    <label class="col-md-2 control-label permissions-model-label" for="folder_to_move">Groups: </label>
		                    <div class="col-md-10">
		                		@foreach($user_groups as $user_group) 
									<span style="display:none" id="file_user_group_selectbox_groupname_{{ $user_group->user_group_id }}">{{ $user_group->user_group_name }}</span>
								@endforeach
								<select name="file_user_groups_selectbox[]" id="file_user_groups_selectbox" class="form-control" tabindex="1" multiple = "multiple">
		                			@foreach($user_groups as $user_group) 
										<option value="{{ $user_group->user_group_id }}">{{ $user_group->user_group_name }}</option>
									@endforeach
		                    	</select>
		                    </div>
		                </div>
                      
                      
		                <div class="form-group">
                         <label class="col-md-2 control-label permissions-model-label" for="folder_to_move">Region: </label>
		                    <div class="col-md-10">
			                	@foreach($region_groups as $region_group) 
									<span style="display:none" id="file_user_region_selectbox_groupname_{{ $region_group->region_id }}">{{ $region_group->region_name }}</span>
								@endforeach
								<select name="file_user_region_selectbox[]" id="file_user_region_selectbox" class="form-control" tabindex="1" multiple = "multiple">
			                		@foreach($region_groups as $region_group) 
										<option value="{{ $region_group->region_id }}">{{ $region_group->region_name }}</option>
									@endforeach
		                    	</select>
		                    </div>
		                </div>
                      
		                <div class="form-group">
		                    <label class="col-md-2 control-label permissions-model-label" for="folder_to_move">Users: </label>
		                    <div class="col-md-10">
								@foreach($users_data as $user_data)
									<span style="display:none" id="file_user_selectbox_username_{{ $user_data->user_id }}">{{ $user_data->username }}</span>
			                    @endforeach
		                    	<select name="file_user_selectbox[]" id="file_user_selectbox" class="form-control" tabindex="2" multiple = "multiple">
										@foreach($users_data as $user_data)
			                    			<option value="{{ $user_data->user_id }}">{{ $user_data->username }}</option>
			                    		@endforeach
		                    	</select>
		                    </div>
		                </div>
		            </div>
		            <div class="modal-footer">
		            	<div class="col-md-6" id="file_user_group_message"></div>
		                <div class="col-md-6">
			                <button type="button" class="btn btn-default close_file_add_user_group_div" data-dismiss="modal">Close</button>
			                <button type="button" name="file_add_user_group_btn" id="file_add_user_group_btn" value="Add User Group" class="btn btn-primary btn-title">Add User / Group</button>
		                </div>
		            </div>
		        </div>
		    </div>
		    </form>
		</div>
		<!-- File Add User / Group Modal -->

		</div>
	</section>
@stop

@section('scripts')
	<style>
	.select2{
		width:100% !important;
	}
	</style>
	<script type="text/javascript" class="init">
		$(document).ready(function() {

			$("#user_groups_selectbox").select2({
				placeholder: "Select a group"
			});			
			$('#region_groups_selectbox').select2({
				placeholder: "Select a region"
			});
			$('#user_selectbox').select2({
				placeholder: "Select a user"
			});
			
			$("#file_user_groups_selectbox").select2({
				placeholder: "Select a group"
			});			
			$('#file_user_region_selectbox').select2({
				placeholder: "Select a region"
			});
			$('#file_user_selectbox').select2({
				placeholder: "Select a user"
			});			
		})	
	    $(function() {
	    	$("#btn_create_folder").click(function() {
				$("#create_folder").modal('show');
				$('#create_folder').on('shown.bs.modal', function() {
	    			$("#folder_name").focus();
				})
			});
			
			$("#btn_upload_file").click(function(){
				$("#upload_file").modal('show');
			});

			$("#download_btn").click(function(){
				$("#open_save").modal('hide');
			});

			$(".permission_link").click(function(){
				$("#permissions").modal('show');
			});

			$(".file_permission_link").click(function(){
				$("#file_permissions").modal('show');
			});

			$("#add_user_group").click(function(){
				$("#user_groups_selectbox").val('').trigger('change');
				$("#region_groups_selectbox").val('').trigger('change');
				$("#user_selectbox").val('').trigger('change');				
				$("#add_user_group_div").modal('show');
				$("#permissions").modal('hide');
			});

			$("#file_add_user_group").click(function(){
				//$('#file_user_groups_selectbox').prop('selectedIndex',0);
				//$('#file_user_region_selectbox').prop('selectedIndex',0);
				//$('#file_user_selectbox').prop('selectedIndex',0);
				$("#file_user_groups_selectbox").val('').trigger('change');
				$("#file_user_region_selectbox").val('').trigger('change');
				$("#file_user_selectbox").val('').trigger('change');
				$('#file_permissions').modal('hide');
			});

			$(".close_add_user_group_div").click(function (e) {
				$("#user_groups_selectbox").val('').trigger('change');
				$("#region_groups_selectbox").val('').trigger('change');
				$("#user_selectbox").val('').trigger('change');
				$("#permissions").modal('show');
			});

	    	$(".close_file_add_user_group_div").click(function (e) {
				$("#file_user_groups_selectbox").val('').trigger('change');
				$("#file_user_region_selectbox").val('').trigger('change');
				$("#file_user_selectbox").val('').trigger('change');				
	    		$("#file_permissions").modal('show');
			});

			$("#user_groups_selectbox").change(function(){
				//alert($('#user_groups_selectbox option:selected').val()); undefined
				if($("#user_groups_selectbox option:selected").val() != undefined)
					$('#user_selectbox').attr("disabled", true);
				else
					$('#user_selectbox').attr("disabled", false);
			});

			$("#user_selectbox").change(function(){
				if($("#user_selectbox option:selected").val() != undefined)
					$('#user_groups_selectbox').attr("disabled", true)
				else
					$('#user_groups_selectbox').attr("disabled", false);
			});

			$("#form_permissions").submit(function() {
		    	$(".dir-read-check-boxes").removeAttr("disabled");
		    	$(".dir-group-read-check-boxes").removeAttr("disabled");
			});

			// File
			$("#file_user_groups_selectbox").change(function(){
				if($("#file_user_groups_selectbox").val() != null)
					$('#file_user_selectbox').attr("disabled", true);
				else
					$('#file_user_selectbox').attr("disabled", false);
			});

			$("#file_user_selectbox").change(function(){
				if($("#file_user_selectbox").val() != null)
					$('#file_user_groups_selectbox').attr("disabled", true)
				else
					$('#file_user_groups_selectbox').attr("disabled", false);
			});

			// Enable R / W checkboxes on form submit
			$("#form_permissions_file").submit(function() {
		    	$(".read-check-boxes").removeAttr("disabled");
		    	$(".group_read-check-boxes").removeAttr("disabled");
			});
			// File

			$("#add_user_group_btn").click(function(){
				var folder_id_val             = $("#hidden_folder_id_for_add_user_groups").val();
				var user_groups_selectbox_val = $("#user_groups_selectbox").val();
				var region_groups_selectbox_val = $("#region_groups_selectbox").val();
				var user_selectbox_val        = $("#user_selectbox").val();
				var user_name_for_permissions = $("#user_selectbox_username_"+user_selectbox_val).text();
				var group_name_for_permissions = $("#user_group_selectbox_groupname_"+user_groups_selectbox_val).text();
				var region_name_for_permissions = $("#region_group_selectbox_groupname_"+region_groups_selectbox_val).text();
				$.ajax({
					type: 'POST',
					url: "<?php echo $appUrl; ?>files/permissions-ajax",
					dataType: "json",
					data: {
						"_token": "{{ csrf_token() }}", 
						"permissions_folder_id": folder_id_val, 
						"permissions_user_group_id": user_groups_selectbox_val, 
						"permissions_region_group_id": region_groups_selectbox_val, 
						"permissions_user": user_selectbox_val
					}
				}).done(function( data ) {
					// var obj = jQuery.parseJSON(data);
					var obj = data;
					if(obj.message){
						$("#permissions").modal('hide');
						$('#user_group_message').html(obj.message);
						$("#user_group_message").addClass("error_message");
						setTimeout(function() { $("#user_group_message").html(""); }, 5000);
					} else {
						var region_name_group_name  = region_name_for_permissions;
						var user_name_group_name    = user_name_for_permissions;
						var group_name_group_name   = group_name_for_permissions;
						var error_model_box         = 0;
						var error_group_name		= '';
						var error_region_name		= '';
						var error_user_name			= '';
						if(user_groups_selectbox_val != null) {
							////// For Each Start
							$.each(user_groups_selectbox_val, function(key,user_custom_val) {
								var user_custom_val  = parseInt(user_custom_val);
								var user_group       = 'Group';
								var user_group_class = 'row group_div';
								var insert_after     = '.group_div';
								var user_group_id    = 'group_div_'+user_custom_val;
								var users_groups_id  = user_custom_val;
								var read_checkbox    = 'group_read_'+user_custom_val;
								var write_checkbox   = 'group_write_'+user_custom_val;
								if($('#'+user_group_id).length > 0) {
									var groupOrgName = $('#'+user_group_id).children().find('label[for="permission_radio_read"]').text();
									var groupRepName = groupOrgName.replace("Group /", "");
									error_model_box   =1;
									error_group_name +=groupRepName+', ';
								}
							});
							////////// For Each End		
						}
						if(region_groups_selectbox_val != null) {
							$.each(region_groups_selectbox_val, function(key,user_custom_val) {
								var user_custom_val  = parseInt(user_custom_val);
								var user_group       = 'Region';
								var user_group_class = 'row region_div';
								var insert_after     = '.region_div';
								var user_group_id    = 'region_div_'+user_custom_val;
								var users_groups_id  = user_custom_val;
								var read_checkbox    = 'group_read_'+user_custom_val;
								var write_checkbox   = 'group_write_'+user_custom_val;
								if($('#'+user_group_id).length > 0) {
									var regionOrgName = $('#'+user_group_id).children().find('label[for="permission_radio_read"]').text();
									var regionRepName = regionOrgName.replace("Region /", "");
									error_model_box   =1;
									error_region_name +=regionRepName+', ';
								}								
							});
						}

						if(user_selectbox_val != null) {
							////// For Each Start
							$.each(user_selectbox_val, function(key,user_custom_val) {
								var user_custom_val  = parseInt(user_custom_val);
								var user_group       = 'User';
								var user_group_class = 'row user_div';
								var insert_after     = '.user_div';
								var user_group_id    = 'user_div_'+user_custom_val;
								var users_groups_id  = user_custom_val;
								var read_checkbox    = 'group_read_'+user_custom_val;
								var write_checkbox   = 'group_write_'+user_custom_val;
								if($('#'+user_group_id).length > 0) {
									var userOrgName = $('#'+user_group_id).children().find('label[for="permission_radio_read"]').text();
									var userRepName = userOrgName.replace("User /", "");
									error_model_box   =1;
									error_user_name +=userRepName+', ';
								}
							});
							////////// For Each End								
						}						
						if(error_model_box == 1){
							$("#permissions").modal('hide');
							var error_group_name1 ='';
							var error_region_name1 ='';
							var error_user_name1 ='';
							if(error_group_name !=''){
								error_group_name1 = error_group_name.replace(/,\s*$/, "");
								error_group_name1 ='Group '+error_group_name1+' already added.<br>'; 
							}
							if(error_region_name !=''){
								error_region_name1 = error_region_name.replace(/,\s*$/, "");
								error_region_name1 ='Region '+error_region_name1+' already added.<br>'; 
							}
							if(error_user_name !=''){
								error_user_name1 = error_user_name.replace(/,\s*$/, "");
								error_user_name1 ='User '+error_user_name1+' already added.<br>'; 
							}							
							$('#user_group_message').html(error_group_name1+error_region_name1+error_user_name1);
							$("#user_group_message").addClass("error_message");
							setTimeout(function() { $("#user_group_message").html(""); }, 5000);
						}else{
							if(user_selectbox_val != null || region_groups_selectbox_val != null || user_groups_selectbox_val != null) {
								if(user_groups_selectbox_val != null) {
									////// For Each Start
									$.each(user_groups_selectbox_val, function(key,user_custom_val) {
										var user_custom_val  = parseInt(user_custom_val);
										var user_group       = 'Group';
										var user_group_class = 'row group_div';
										var insert_after     = '.group_div';
										var user_group_id    = 'group_div_'+user_custom_val;
										var users_groups_id  = user_custom_val;
										var read_checkbox    = 'group_read_'+user_custom_val;
										var write_checkbox   = 'group_write_'+user_custom_val;
										
										var str_row = '<div class="' + user_group_class + '" id="'+user_group_id+'">\
										<div class="col-md-6">\
										<label for="permission_radio_read">' + user_group + ' / ' +$('#user_group_selectbox_groupname_'+user_custom_val).text()+ ' </label>\
										</div>\
										<div class="col-md-3">\
										<input type="checkbox" name="permissions['+user_group+']['+users_groups_id+'][read]" value="1" id="'+read_checkbox+'" class="dir-read-check-boxes"><span>R</span>\
										<input type="checkbox" name="permissions['+user_group+']['+users_groups_id+'][write]" class="margin-left-20" value="1" id="'+write_checkbox+'" onclick="dir_modal_box_disable_read(\''+read_checkbox+'\')"><span>W</span>\
										</div>\
										<div class="col-md-3"><a href="javascript: void(0)" onclick="remove_div(\''+user_group_id+'\')">Delete</a>\
										</div>\
										</div>';
										$(insert_after).last().after(str_row);
									});
									////////// For Each End		
								}								

								if(region_groups_selectbox_val != null) {
									$.each(region_groups_selectbox_val, function(key,user_custom_val) {
										var user_custom_val  = parseInt(user_custom_val);
										var user_group       = 'Region';
										var user_group_class = 'row region_div';
										var insert_after     = '.region_div';
										var user_group_id    = 'region_div_'+user_custom_val;
										var users_groups_id  = user_custom_val;
										var read_checkbox    = 'group_read_'+user_custom_val;
										var write_checkbox   = 'group_write_'+user_custom_val;

										var str_row = '<div class="' + user_group_class + '" id="'+user_group_id+'">\
										<div class="col-md-6">\
										<label for="permission_radio_read">' + user_group + ' / ' + $('#region_group_selectbox_groupname_'+user_custom_val).text() + ' </label>\
										</div>\
										<div class="col-md-3">\
										<input type="checkbox" name="permissions['+user_group+']['+users_groups_id+'][read]" value="1" id="'+read_checkbox+'" class="dir-read-check-boxes"><span>R</span>\
										<input type="checkbox" name="permissions['+user_group+']['+users_groups_id+'][write]" class="margin-left-20" value="1" id="'+write_checkbox+'" onclick="dir_modal_box_disable_read(\''+read_checkbox+'\')"><span>W</span>\
										</div>\
										<div class="col-md-3"><a href="javascript: void(0)" onclick="remove_div(\''+user_group_id+'\')">Delete</a>\
										</div>\
										</div>';
										$(insert_after).last().after(str_row);
									});
								}
								
								if(user_selectbox_val != null) {
									////// For Each Start
									$.each(user_selectbox_val, function(key,user_custom_val) {
										var user_custom_val  = parseInt(user_custom_val);
										var user_group       = 'User';
										var user_group_class = 'row user_div';
										var insert_after     = '.user_div';
										var user_group_id    = 'user_div_'+user_custom_val;
										var users_groups_id  = user_custom_val;
										var read_checkbox    = 'group_read_'+user_custom_val;
										var write_checkbox   = 'group_write_'+user_custom_val;
										
										var str_row = '<div class="' + user_group_class + '" id="'+user_group_id+'">\
										<div class="col-md-6">\
										<label for="permission_radio_read">' + user_group + ' / ' + $('#user_selectbox_username_'+user_custom_val).text() + ' </label>\
										</div>\
										<div class="col-md-3">\
										<input type="checkbox" name="permissions['+user_group+']['+users_groups_id+'][read]" value="1" id="'+read_checkbox+'" class="dir-read-check-boxes"><span>R</span>\
										<input type="checkbox" name="permissions['+user_group+']['+users_groups_id+'][write]" class="margin-left-20" value="1" id="'+write_checkbox+'" onclick="dir_modal_box_disable_read(\''+read_checkbox+'\')"><span>W</span>\
										</div>\
										<div class="col-md-3"><a href="javascript: void(0)" onclick="remove_div(\''+user_group_id+'\')">Delete</a>\
										</div>\
										</div>';
										$(insert_after).last().after(str_row);
									});
									////////// For Each End								
								}								

								$('#user_selectbox').prop('selectedIndex',0);
								$('#user_selectbox').attr("disabled", false);
								$('#user_groups_selectbox').prop('selectedIndex',0);
								$('#user_groups_selectbox').attr("disabled", false);
								$('#region_groups_selectbox').prop('selectedIndex',0);
								$('#region_groups_selectbox').attr("disabled", false);
								$("#add_user_group_div").modal('hide');
								$("#permissions").modal('show');
								
							}                  
						}
					}
               
				});
			});

			// File
			$("#file_add_user_group_btn").click(function(){
				var file_id_val                     = $("#hidden_file_id_for_add_user_groups").val();
				var file_user_groups_selectbox_val  = $("#file_user_groups_selectbox").val();
				var file_user_region_selectbox_val  = $("#file_user_region_selectbox").val();

				var file_user_selectbox_val         = $("#file_user_selectbox").val();
				var file_user_name_for_permissions  = $("#file_user_selectbox_username_"+file_user_selectbox_val).text();

				var file_group_name_for_permissions = $("#file_user_group_selectbox_groupname_"+file_user_groups_selectbox_val).text();

				var file_region_name_for_permissions = $("#file_user_region_selectbox_groupname_"+file_user_region_selectbox_val).text();

				$.ajax({
					type: 'POST',
					url: "<?php echo $appUrl; ?>files/permissions-ajax",
					dataType: "json",
					data: {
						"_token": "{{ csrf_token() }}", 
						"file_permissions_file_id": file_id_val, 
						"file_permissions_user_group_id": file_user_groups_selectbox_val, 
						"file_permissions_user": file_user_selectbox_val
					}
				}).done(function( data ) {
					// var obj = jQuery.parseJSON(data);
					var obj = data;
					if(obj.file_message){
						$("#file_permissions").modal('hide');
						$('#file_user_group_message').html(obj.file_message);
						$("#file_user_group_message").addClass("error_message");
						setTimeout(function() { $("#file_user_group_message").html(""); }, 5000);
					} else {
						var file_error_model_box    = 0;
						var error_group_name		= '';
						var error_region_name		= '';
						var error_user_name			= '';						
						//alert('KKKKK');
						if(file_user_groups_selectbox_val != null) {
							////// For Each Start
							$.each(file_user_groups_selectbox_val, function(key,user_custom_val) {
								var user_custom_val  = parseInt(user_custom_val);
								var file_user_group = 'Group';
								var file_user_group_class = 'row file_group_div';
								var file_insert_after = '.file_group_div';
								var file_user_group_id = 'file_group_div_'+user_custom_val;
								var file_users_groups_id = user_custom_val;

								var file_read_checkbox = 'group_file_read_'+user_custom_val;
								var file_write_checkbox = 'group_file_write_'+user_custom_val;
								
								if($('#'+file_user_group_id).length > 0) {
									var groupOrgName = $('#'+file_user_group_id).children().find('label[for="permission_radio_read"]').text();
									var groupRepName = groupOrgName.replace("Group /", "");
									file_error_model_box   =1;
									error_group_name +=groupRepName+', ';
								}
							});
							////////// For Each End							
						}
						//alert(file_user_region_selectbox_val);
						if(file_user_region_selectbox_val != null){
							////// For Each Start
							$.each(file_user_region_selectbox_val, function(key,user_custom_val) {
								var user_custom_val  = parseInt(user_custom_val);
								var file_user_group = 'Region';
								var file_user_group_class = 'row file_region_div';
								var file_insert_after = '.file_region_div';
								var file_user_group_id = 'file_region_div_'+user_custom_val;
								var file_users_groups_id = user_custom_val;

								var file_read_checkbox = 'region_file_read_'+user_custom_val;
								var file_write_checkbox = 'region_file_write_'+user_custom_val;
								if($('#'+file_user_group_id).length > 0) {
									var regionOrgName = $('#'+file_user_group_id).children().find('label[for="permission_radio_read"]').text();
									var regionRepName = regionOrgName.replace("Region /", "");
									file_error_model_box   =1;
									error_region_name +=regionRepName+', ';
								}
							});
						}

						if(file_user_selectbox_val != null){
							////// For Each Start
							$.each(file_user_selectbox_val, function(key,user_custom_val) {
								var user_custom_val  = parseInt(user_custom_val);
								var file_user_group = 'User';
								var file_user_group_class = 'row file_user_div';
								var file_insert_after = '.file_user_div';
								var file_user_group_id = 'file_user_div_'+user_custom_val;
								var file_users_groups_id = user_custom_val;

								var file_read_checkbox = 'file_read_'+user_custom_val;
								var file_write_checkbox = 'file_write_'+user_custom_val;
								if($('#'+file_user_group_id).length > 0) {
									var userOrgName = $('#'+file_user_group_id).children().find('label[for="permission_radio_read"]').text();
									var userRepName = userOrgName.replace("User /", "");
									file_error_model_box   =1;
									error_user_name +=userRepName+', ';									
								} 
							});
						}						
						if(file_error_model_box == 1){
							$("#file_permissions").modal('hide');
							var error_group_name1 ='';
							var error_region_name1 ='';
							var error_user_name1 ='';
							if(error_group_name !=''){
								error_group_name1 = error_group_name.replace(/,\s*$/, "");
								error_group_name1 ='Group '+error_group_name1+' already added.<br>'; 
							}
							if(error_region_name !=''){
								error_region_name1 = error_region_name.replace(/,\s*$/, "");
								error_region_name1 ='Region '+error_region_name1+' already added.<br>'; 
							}
							if(error_user_name !=''){
								error_user_name1 = error_user_name.replace(/,\s*$/, "");
								error_user_name1 ='User '+error_user_name1+' already added.<br>'; 
							}							
							$('#file_user_group_message').html(error_group_name1+error_region_name1+error_user_name1);
							$("#file_user_group_message").addClass("error_message");
							setTimeout(function() { $("#file_user_group_message").html(""); }, 5000);
						}else{
							if(file_user_selectbox_val != null || file_user_region_selectbox_val != null || file_user_groups_selectbox_val != null) {
								
								if(file_user_groups_selectbox_val != null) {
									////// For Each Start
									$.each(file_user_groups_selectbox_val, function(key,user_custom_val) {
										var user_custom_val  = parseInt(user_custom_val);
										var file_user_group = 'Group';
										var file_user_group_class = 'row file_group_div';
										var file_insert_after = '.file_group_div';
										var file_user_group_id = 'file_group_div_'+user_custom_val;
										var file_users_groups_id = user_custom_val;

										var file_read_checkbox = 'group_file_read_'+user_custom_val;
										var file_write_checkbox = 'group_file_write_'+user_custom_val;
										
										var file_str_row = '<div class="' + file_user_group_class + '" id="'+file_user_group_id+'">\
										<div class="col-md-4">\
										<label for="permission_radio_read">' + file_user_group + ' / ' + $('#file_user_group_selectbox_groupname_'+user_custom_val).text() + ' </label>\
										</div>\
										<div class="col-md-3">\
										<input class="read-check-boxes" type="checkbox" name="file_permissions['+file_user_group+']['+file_users_groups_id+'][read]" value="1" id="'+file_read_checkbox+'"><span>R</span>\
										<input type="checkbox" name="file_permissions['+file_user_group+']['+file_users_groups_id+'][write]" class="margin-left-20" value="1" id="'+file_write_checkbox+'" onclick="disable_read(\''+file_read_checkbox+'\')"><span>W</span>\
										</div>\
										<div class="col-md-2"><a href="javascript: void(0)" onclick="file_remove_div(\''+file_user_group_id+'\')">Delete</a>\
										</div>\
										</div>';
										$(file_insert_after).last().after(file_str_row);
									});
									////////// For Each End							
								}


								if(file_user_region_selectbox_val != null){
									////// For Each Start
									$.each(file_user_region_selectbox_val, function(key,user_custom_val) {
										var user_custom_val  = parseInt(user_custom_val);
										var file_user_group = 'Region';
										var file_user_group_class = 'row file_region_div';
										var file_insert_after = '.file_region_div';
										var file_user_group_id = 'file_region_div_'+user_custom_val;
										var file_users_groups_id = user_custom_val;

										var file_read_checkbox = 'region_file_read_'+user_custom_val;
										var file_write_checkbox = 'region_file_write_'+user_custom_val;

										var file_str_row = '<div class="' + file_user_group_class + '" id="'+file_user_group_id+'">\
										<div class="col-md-4">\
										<label for="permission_radio_read">' + file_user_group + ' / ' + $('#file_user_region_selectbox_groupname_'+user_custom_val).text() + ' </label>\
										</div>\
										<div class="col-md-3">\
										<input class="read-check-boxes" type="checkbox" name="file_permissions['+file_user_group+']['+file_users_groups_id+'][read]" value="1" id="'+file_read_checkbox+'"><span>R</span>\
										<input type="checkbox" name="file_permissions['+file_user_group+']['+file_users_groups_id+'][write]" class="margin-left-20" value="1" id="'+file_write_checkbox+'" onclick="disable_read(\''+file_read_checkbox+'\')"><span>W</span>\
										</div>\
										<div class="col-md-2"><a href="javascript: void(0)" onclick="file_remove_div(\''+file_user_group_id+'\')">Delete</a>\
										</div>\
										</div>';
										$(file_insert_after).last().after(file_str_row);

									});
								}
								
								
								if(file_user_selectbox_val != null){
									////// For Each Start
									$.each(file_user_selectbox_val, function(key,user_custom_val) {
										var user_custom_val  = parseInt(user_custom_val);
										var file_user_group = 'User';
										var file_user_group_class = 'row file_user_div';
										var file_insert_after = '.file_user_div';
										var file_user_group_id = 'file_user_div_'+user_custom_val;
										var file_users_groups_id = user_custom_val;

										var file_read_checkbox = 'file_read_'+user_custom_val;
										var file_write_checkbox = 'file_write_'+user_custom_val;
										

										var file_str_row = '<div class="' + file_user_group_class + '" id="'+file_user_group_id+'">\
										<div class="col-md-4">\
										<label for="permission_radio_read">' + file_user_group + ' / ' + $('#file_user_selectbox_username_'+user_custom_val).text() + ' </label>\
										</div>\
										<div class="col-md-3">\
										<input class="read-check-boxes" type="checkbox" name="file_permissions['+file_user_group+']['+file_users_groups_id+'][read]" value="1" id="'+file_read_checkbox+'"><span>R</span>\
										<input type="checkbox" name="file_permissions['+file_user_group+']['+file_users_groups_id+'][write]" class="margin-left-20" value="1" id="'+file_write_checkbox+'" onclick="disable_read(\''+file_read_checkbox+'\')"><span>W</span>\
										</div>\
										<div class="col-md-2"><a href="javascript: void(0)" onclick="file_remove_div(\''+file_user_group_id+'\')">Delete</a>\
										</div>\
										</div>';
										$(file_insert_after).last().after(file_str_row);										
									});
								}								
								
								$('#file_user_selectbox').prop('selectedIndex',0);
								$('#file_user_selectbox').attr("disabled", false);
								
								$('#file_user_groups_selectbox').prop('selectedIndex',0);
								$('#file_user_groups_selectbox').attr("disabled", false);
								
								$('#file_user_region_selectbox').prop('selectedIndex',0);
								$('#file_user_region_selectbox').attr("disabled", false);
								
								$("#file_add_user_group_div").modal('hide');
								$("#file_permissions").modal('show');
							}
						}
					}
				});
			});
			// File

	    });

		function open_save(id = null) {
			$(".class_open_save").remove();
			$('<input>').attr({
			    type: 'hidden',
			    value: id,
			    name: 'hidden_file_id',
			    class: 'class_open_save'
			}).appendTo('#form_open_save');
			$("#open_save").modal('show');
		}

		function move_file(id = null) {
			$(".class_move_file").remove();
			$(".dynamic-title").text('Move File');
			$(".move_file_btn").text('Move File');
			$(".move_file_btn").val('Move File');
			$('<input>').attr({
			    type: 'hidden',
			    value: id,
			    name: 'hidden_move_file_id',
			    class: 'class_move_file'
			}).appendTo('#form_move_file');
			$("#form_move_file").prop('action', '<?php echo $appUrl; ?>files/move-file');
			$("#file_move").modal('show');
		}

		function move_folder(id = null) {
			$(".class_move_file").remove();
			$(".dynamic-title").text('Move Folder');
			$(".move_file_btn").text('Move Folder');
			$(".move_file_btn").val('Move Folder');
			$('<input>').attr({
			    type: 'hidden',
			    value: id,
			    name: 'hidden_move_folder_id',
			    class: 'class_move_file'
			}).appendTo('#form_move_file');
			$("#form_move_file").prop('action', '<?php echo $appUrl; ?>files/move-directory');
			$("#file_move").modal('show');
		}

		function permissions(id = null) {
			$(".class_permission").remove();
			$(".dir_add_user_group").val("Add User / Group");
			$(".dir_set_permission").val("Set Permission");
			$('<input>').attr({
			    type: 'hidden',
			    value: id,
			    name: 'hidden_folder_id_for_permission',
			    class: 'class_permission'
			}).appendTo('#form_permissions');

			$(".class_add_user_groups").remove();
			$('<input>').attr({
			    type: 'hidden',
			    value: id,
			    id: 'hidden_folder_id_for_add_user_groups',
			    name: 'hidden_folder_id_for_add_user_groups',
			    class: 'class_add_user_groups'
			}).appendTo('#form_add_user_group');

			$.ajax({
				type: 'POST',
				url: "<?php echo $appUrl; ?>files/permissions-ajax",
				dataType: "json",
				data : { 
			        "_token": "{{ csrf_token() }}",
					"dir_id": id,
				}
			}).done(function( data ) {
				// var obj = JSON.parse(data);
				var obj = data;
				if(obj.folder_location) {
					$('#folder_title').html(obj.folder_title);
					$('#folder_location').html(obj.folder_location);
					$('#folders_files_count').html(obj.folders_files_count);
					$('#folder_size').html(obj.folder_size);
					$('#folder_last_modified').html(obj.folder_last_modified);
					$('#user_str').html(obj.user_str);

					// Add User / Group Modal Box
					$('#add_user_group_folder_location').html(obj.add_user_group_folder_location);
					$('#add_user_group_folder_name').val(obj.add_user_group_folder_location);

					$('.dir_write_check_boxes').each(function(i, obj) {
						//alert(obj.id);
						var split_write_id = obj.id.split("write_");
						var read_checkbox_id = 'read_'+split_write_id[1];

			    		if($('#'+obj.id+':checkbox:checked').length > 0) {
							$('#'+read_checkbox_id).attr("disabled", true);
						}
					});

					$('.dir_group_write_check_boxes').each(function(i, obj) {
						//alert(obj.id);
						var split_write_id = obj.id.split("_write_");
						var read_checkbox_id = 'group_read_'+split_write_id[1];
			    		//alert(file_read_checkbox_id);
			    		if($('#'+obj.id+':checkbox:checked').length > 0) {
							$('#'+read_checkbox_id).attr("disabled", true);
						}
					});
				}
			});
		}

		function file_permissions(folder_id = null, file_id = null) {
			$(".class_permission_file").remove();
			$(".dir_add_user_group").val("Add User / Group");
			$(".dir_set_permission").val("Set Permission");
			$('<input>').attr({
			    type: 'hidden',
			    value: file_id,
			    name: 'hidden_file_id_for_permission',
			    class: 'class_permission_file'
			}).appendTo('#form_permissions_file');

			$(".class_add_user_groups_file").remove();
			$('<input>').attr({
			    type: 'hidden',
			    value: file_id,
			    id: 'hidden_file_id_for_add_user_groups',
			    name: 'hidden_file_id_for_add_user_groups',
			    class: 'class_add_user_groups_file'
			}).appendTo('#file_form_add_user_group');

			$.ajax({
				type: 'POST',
				url: "<?php echo $appUrl; ?>files/permissions-ajax",
				dataType: "json",
				data: {
			        "_token": "{{ csrf_token() }}",
					"file_folder_id": folder_id, 
					"file_id": file_id, 
					"dir_type": '{{ $dir_type }}', 
					"d_type": '{{ $d_type }}',
				}
			}).done(function( data ) {
				// var obj = jQuery.parseJSON(data);
				var obj = data;
				if(obj.file_title) {
					$('#file_title').html(obj.file_title);
					$('#file_location').html(obj.file_location);
					$('#file_size').html(obj.file_size);
					$('#file_last_modified').html(obj.file_last_modified);
					$('#file_user_str').html(obj.file_user_str);

					// Add User / Group Modal Box
					$('#add_user_group_file_title').html(obj.add_user_group_file_title);
					$('#add_user_group_file_name').val(obj.add_user_group_file_title);

					$('.write_check_boxes').each(function(i, obj) {
						//alert(obj.id);
						var split_file_write_id = obj.id.split("_write_");
						var file_read_checkbox_id = 'file_read_'+split_file_write_id[1];

			    		if($('#'+obj.id+':checkbox:checked').length > 0) {
							$('#'+file_read_checkbox_id).attr("disabled", true);
						}
					});

					$('.group_write_check_boxes').each(function(i, obj) {
						//alert(obj.id);
						var split_file_write_id = obj.id.split("_file_write_");
						var file_read_checkbox_id = 'group_file_read_'+split_file_write_id[1];
			    		//alert(file_read_checkbox_id);
			    		if($('#'+obj.id+':checkbox:checked').length > 0) {
							$('#'+file_read_checkbox_id).attr("disabled", true);
						}
					});
				}
			});
		}

		function add_users_groups() {
			/*$(".class_move_file").remove();
			$(".dynamic-title").text('Move Folder');
			$(".btn-title").text('Move Folder');
			$(".btn-title").val('Move Folder');
			$('<input>').attr({
			    type: 'hidden',
			    value: id,
			    name: 'hidden_move_folder_id',
			    class: 'class_move_file'
			}).appendTo('#form_move_file');*/
			$("#add_user_group_div").modal('show');
		}

		function dir_modal_box_disable_read(id) {
			var user_or_group = id.slice(0,3);
			var split_read = id.split("read_");

			if(user_or_group == 'rea' && $('#write_'+split_read[1]+':checkbox:checked').length > 0) {
				//console.log('user => ' + id);
				$('#'+id).prop('checked', true);
				$('#'+id).attr("disabled", true);
			}
			else if(user_or_group == 'gro' && $('#group_write_'+split_read[1]+':checkbox:checked').length > 0) {
				//console.log('group => ' + id);
				$('#'+id).prop('checked', true);
				$('#'+id).attr("disabled", true);
			} else {
				//console.log(id);
				$('#'+id).attr("disabled", false);
			}
		}

		function disable_read(id) {
			var user_or_group = id.slice(0,3);
			var split_file_read = id.split("_read_");

			if(user_or_group == 'fil' && $('#file_write_'+split_file_read[1]+':checkbox:checked').length > 0) {
				//console.log('user => ' + id);
				$('#'+id).prop('checked', true);
				$('#'+id).attr("disabled", true);
			}
			else if(user_or_group == 'gro' && $('#group_file_write_'+split_file_read[1]+':checkbox:checked').length > 0) {
				//console.log('group => ' + id);
				$('#'+id).prop('checked', true);
				$('#'+id).attr("disabled", true);
			} else {
				//console.log(id);
				$('#'+id).attr("disabled", false);
			}
		}

		function remove_div(id, folder_id) {
			var split_user_group = id.split("_div_");
			var user_group_id = split_user_group[1];
			var user_group_type = split_user_group[0];
			$.ajax({
				type: 'POST',
				url: "<?php echo $appUrl; ?>files/permissions-ajax",
				dataType: "json",
				data: { 
					"_token": "{{ csrf_token() }}",
					"user_group_id": user_group_id, 
					"user_group_type": user_group_type, 
					"folder_id": folder_id
				}
			}).done(function( data ) {});
			$('#'+id).hide('slow', function(){ $('#'+id).remove(); });
		}

		function file_remove_div(id, file_id) {
			var split_user_group = id.split("_div_");
			var user_group_id = split_user_group[1];
			var user_group_type_split = split_user_group[0].split("_");
			var user_group_type = user_group_type_split[1];
			$.ajax({
				type: 'POST',
				url: "<?php echo $appUrl; ?>files/permissions-ajax",
				dataType: "json",
				data: {
					"_token": "{{ csrf_token() }}",
					"user_group_id": user_group_id, 
					"user_group_type": user_group_type, 
					"file_id": file_id
				}
			}).done(function( data ) {});
			$('#'+id).hide('slow', function(){ $('#'+id).remove(); });
		}
    </script>
@stop
