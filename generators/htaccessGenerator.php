<?php
class HtaccessGenerator extends Generator
{
	public function generate()
	{
		$this->source = $this->htaccess();
	}
	
	public function write($filename='', $source=false)
	{
		if('' === $filename)
		{
			$filename = $this->project->projectRoot . $this->project->ds . 'www' . $this->project->ds . '.htaccess';
		}
		parent::write($filename);
	}
	
	private function htaccess()
	{
		$htaccess = <<<HTACCESS
RewriteEngine on
AcceptPathInfo on

# rewrites url if the requested file doesn't exist

RewriteCond %{REQUEST_FILENAME} !-f
#RewriteRule ^(.*)$ /home/adamnfish/Dev/bene_projects/TestProject/www/index.php?%{QUERY_STRING} [L]
RewriteRule ^.*$ /home/adamnfish/Dev/bene_projects/TestProject/www/index.php%{REQUEST_URI}?%{QUERY_STRING} [L]
HTACCESS;
		return $htaccess;
	}
}
?>