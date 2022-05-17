<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model {

	public $table = "file_system";

	const CREATED_AT = 'date_created';

	const UPDATED_AT = 'date_modified';

	protected $fillable = ['parent_dir_id', 'dir_path', 'user_id_read', 'user_id_write', 'group_id_read', 'group_id_write','region_id_read', 'region_id_write', 'date_modified'];
}
