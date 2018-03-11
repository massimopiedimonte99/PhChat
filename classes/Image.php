<?php

namespace Classes;

class Image 
{
	public static $error = FALSE;

	public static function hasProfileImage($user_id)
	{
		if(DB::_query('SELECT img_path FROM imgs WHERE user_id=:user_id', [ 'user_id' => $user_id ])) {
			if(DB::_query('SELECT img_path FROM imgs WHERE user_id=:user_id', [ 'user_id' => $user_id ])[0]['img_path'] != 'assets/avatars/profile-default.png') {
				return true;
			}
		}

		return false;
	}	

	public static function uploadImage($file, $img_path)
	{
		$file_name 		= $file['name'];
		$file_tmp_name 	= $file['tmp_name'];
		$file_error		= $file['error'];
		$file_size		= $file['size'];

		$file_extension = explode('.', $file_name);
		$file_escaped_extension = strtolower(end($file_extension));

		$allowed = array('jpg', 'jpeg', 'png');

		if(in_array($file_escaped_extension, $allowed)) {
			if($file_size < 1000000) {
				if($file_error === 0) {

					switch (exif_imagetype($file_tmp_name)) {
					    case IMAGETYPE_PNG:
					        $imageTmp = imagecreatefrompng($file_tmp_name); break;
					    case IMAGETYPE_JPEG:
					        $imageTmp = imagecreatefromjpeg($file_tmp_name); break;
					    case IMAGETYPE_GIF:
					        $imageTmp = imagecreatefromgif($file_tmp_name); break;
					    case IMAGETYPE_BMP:
					        $imageTmp = imagecreatefrombmp($file_tmp_name); break;
					    default:
					        $imageTmp = imagecreatefromjpeg($file_tmp_name); break;
					}

					imagejpeg($imageTmp, $img_path, 70);
					imagedestroy($imageTmp);

					self::$error = FALSE;

				} else { 
					echo "Something went wrong while processing your request";
					self::$error = TRUE;
				}
			} else { 
				echo "Max file size (1MB) exceeded";
				self::$error = TRUE;
			}
		} else { 
			echo "Invalid image format";
			self::$error = TRUE;
		}
	}

}