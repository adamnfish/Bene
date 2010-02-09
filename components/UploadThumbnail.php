<?php
class UploadThumbnail extends Component
{
	public $allowed = array("jpg", "jpeg", "png", "gif");
	private $img;
	private $extension;
	
	public function generateThumbnail($fieldname, $width, $height)
	{
		$upload_info = $_FILES[$fieldname];
		$file_info = pathinfo($upload_info['name']);
		$extension = strtolower($file_info['extension']);
		
		if(is_uploaded_file($upload_info['tmp_name']))
		{
			if(true === in_array($extension, $this->allowed))
			{
				if($thumb = new Imagick($upload_info['tmp_name']))
				{
					$thumb->cropThumbnailImage(170,170);
					$this->extension = $extension;
					$this->img = $thumb;
					return $this->img;
				}
				else
				{
					$this->E->throwErr(3, 'Couldn\'t create Imagick instance from image');
					return false;
				}
			}
			else
			{
				$this->E->throwErr(3, 'Uploaded file provided to ' . __CLASS__ . '::' . __METHOD__ . ' was not an image');
				return false;
			}
		}
		else
		{
			$this->E->throwErr(3, 'Non-uploaded file provided to ' . __CLASS__ . '::' . __METHOD__);
			return false;
		}
	}
	
	public function getExtension()
	{
		return $this->extension;
	}
	
	public function write($filename)
	{
		return $this->img->writeImage($filename);
	}
}
?>