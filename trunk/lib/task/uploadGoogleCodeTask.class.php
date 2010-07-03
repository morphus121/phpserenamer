<?php
/**
 *
 * @see http://support.googlecode.com/svn/trunk/scripts/googlecode_upload.py
 *
 * @author adriengallou
 *
 */
class uploadGoogleCodeTask extends sfBaseTask
{

    /**
   * (non-PHPdoc)
   *
   * @see lib/vendor/symfony/task/sfTask#configure()
   * @return void
   */
  protected function configure()
  {
    $this->addArgument('file', sfCommandArgument::REQUIRED, 'Fiile to upload');
    $this->addArgument('project', sfCommandArgument::REQUIRED, 'Project name');

    $this->namespace           = '';
    $this->name                = 'googlecode-upload';
    $this->briefDescription    = 'Upload a file to a google code project';
    $this->detailedDescription = 'Upload a file to a google code project';

    $this->addOption('user', null, sfCommandOption::PARAMETER_OPTIONAL, 'user (without @google.com)');
    $this->addOption('password', null, sfCommandOption::PARAMETER_OPTIONAL, 'password (google code password, not google account password)');
    $this->addOption('summary', null, sfCommandOption::PARAMETER_OPTIONAL, 'summary for uploaded file');
    $this->addArgument('labels', sfCommandArgument::IS_ARRAY, 'labels to add to uploaded file');
  }

  /**
   * (non-PHPdoc)
   *
   * @param string[] $arguments arguments
   * @param string[] $options   options
   *
   * @see lib/vendor/symfony/task/sfTask#execute()
   * @return int
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (is_null($options['summary']))
    {
      $options['summary'] = $this->ask('Summary');
    }
    if (is_null($options['user']))
    {
      $options['user'] = $this->ask('User');
    }
    if (is_null($options['password']))
    {
      $options['password'] = $this->ask('Password');
    }
    $adapterOptions = array(
      'SSL_VERIFYPEER' => false,
      'RETURNTRANSFER' => 1,
    );
    $browser    = new sfWebBrowser(array(), 'sfCurlAdapter', $adapterOptions);

    $formFields = array('summary' => $options['summary']);

    list ($contentType, $body) = $this->encodeUploadRequest($formFields, new SplFileInfo($arguments['file']));

    $uploadUri = sprintf('https://%s.googlecode.com/files', $arguments['project']);
    $authToken = base64_encode(sprintf('%s:%s', $options['user'], $options['password']));

    $headers   = array(
      'Authorization'  => sprintf('Basic %s', $authToken),
      'User-Agent'     => 'Googlecode.com uploader v0.9.4',
      'Content-Type'   => $contentType,
      'Content-Length' => strlen($body),
    );

    $browser->post($uploadUri, $body, $headers);
    $statusCode = $browser->getResponseCode();
    if ($statusCode == 201)
    {
      $this->logSection('Upload bien effectué', $arguments['file']);
    }
    else
    {
      $message = $browser->getResponseMessage() . $browser->getResponseBody();
      throw new sfException(sprintf('%s "%s" %s', $statusCode, trim($message), $uploadUri));
    }
  }

  /**
   *
   * @param array       $formFields
   * @param splFileInfo $file
   *
   * @return array
   */
  protected function encodeUploadRequest(array $formFields, SplFileInfo $file)
  {
    $boundary    = '-----------Googlecode_boundary_reindeer_flotilla';
    $crlf        = "\r\n";
    $fileContent = file_get_contents($file->getRealPath());

    $body = array();
    foreach ($formFields as $key => $value)
    {
      $body[] = '--' . $boundary;
      $body[] = sprintf('Content-Disposition: form-data; name="%s"', $key);
      $body[] = '';
      $body[] = $value;
    }
    $body[] = '--' . $boundary;
    $body[] = sprintf('Content-Disposition: form-data; name="filename"; filename="%s"', $file->getBasename());
    $body[] = 'Content-Type: application/octet-stream';
    $body[] = '';
    $body[] = $fileContent;
    $body[] = '--' . $boundary . '--';
    $body[] = '';

    $body = implode($crlf, $body);
    return array(
      'multipart/form-data; boundary=' . $boundary,
      $body,
    );
  }

}