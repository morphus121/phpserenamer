<?php
class srCoverageReportTask extends sfBaseTask
{
  /**
   * (non-PHPdoc)
   * @see lib/vendor/symfony/task/sfTask#configure()
   */
  protected function configure()
  {
    $this->namespace           = 'sr-test';
    $this->name                = 'coverage-report';
    $this->briefDescription    = 'Crée un rapport du coverage des tests unitaires pour emma';
    $this->detailedDescription = 'Crée un rapport du coverage des tests unitaires pour emma';

    $this->addOption('xml', null, sfCommandOption::PARAMETER_REQUIRED, 'Chemin ou sauver le fichier xml');
  }

    /**
   * (non-PHPdoc)
   * @see lib/vendor/symfony/task/sfTask#execute()
   */
  protected function execute($arguments = array(), $options = array())
  {
    $coverage = $this->getCoveragePercentByFile();
    file_put_contents($options['xml'], $this->toXmlEmma($coverage));
  }

  protected function toXmlEmma(array $coverageByFile)
  {

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $dom->appendChild($dom->createComment(' EMMA report, generated ' . date('r') . ' '));
    $dom->appendChild($report = $dom->createElement('report'));

    $stats = $dom->createElement('stats');

    $packages = $dom->createElement('packages');
    $classes  = $dom->createElement('classes');
    $methods  = $dom->createElement('methods');
    $srclines = $dom->createElement('srclines');
    $srcfiles = $dom->createElement('srcfiles');

    $packages->setAttribute('value', 2);
    $classes->setAttribute('value', 0);
    $methods->setAttribute('value', 0);

    $stats->appendChild($packages);
    $stats->appendChild($classes);
    $stats->appendChild($methods);
    $stats->appendChild($srcfiles);
    $stats->appendChild($srclines);

    $report->appendChild($stats);

    $data = $dom->createElement('data');
    $all = $dom->createElement('all');
    $all->setAttribute('name', 'all classes');
    $data->appendChild($all);
    $report->appendChild($data);

    $coverageClass = $dom->createElement('coverage');
    $coverageClass->setAttribute('type', 'class, %');
    $coverageClass->setAttribute('value', '0% (0/0)');
    $all->appendChild($coverageClass);

    $coverageMethod = $dom->createElement('coverage');
    $coverageMethod->setAttribute('type', 'method, %');
    $coverageMethod->setAttribute('value', '0% (0/0)');
    $all->appendChild($coverageMethod);

    $coverageBlock = $dom->createElement('coverage');
    $coverageBlock->setAttribute('type', 'block, %');
    $coverageBlock->setAttribute('value', '0% (0/0)');
    $all->appendChild($coverageBlock);

    $elementTotalLines = $dom->createElement('coverage');
    $elementTotalLines->setAttribute('type', 'line, %');
    $all->appendChild($elementTotalLines);

    $dirs = array();
    foreach (array_keys($coverageByFile) as $file)
    {
      $dirs[] = pathinfo($file, PATHINFO_DIRNAME);
    }
    $dirs = array_unique($dirs);

    $packagesTab = array();
    $totalLinesPackageElements = array();
    foreach ($dirs as $dir)
    {
      $coverageClass = $dom->createElement('coverage');
      $coverageClass->setAttribute('type', 'class, %');
      $coverageClass->setAttribute('value', '0% (0/0)');

      $coverageMethod = $dom->createElement('coverage');
      $coverageMethod->setAttribute('type', 'method, %');
      $coverageMethod->setAttribute('value', '0% (0/0)');

      $coverageBlock = $dom->createElement('coverage');
      $coverageBlock->setAttribute('type', 'block, %');
      $coverageBlock->setAttribute('value', '0% (0/0)');

      $totalLinesPackageElements[$dir] = $dom->createElement('coverage');
      $totalLinesPackageElements[$dir]->setAttribute('type', 'line, %');

      $packagesTab[$dir] = $dom->createElement('package');
      $packagesTab[$dir]->setAttribute('name', $dir);
      $packagesTab[$dir]->appendChild($coverageClass);
      $packagesTab[$dir]->appendChild($coverageMethod);
      $packagesTab[$dir]->appendChild($coverageBlock);
      $packagesTab[$dir]->appendChild($totalLinesPackageElements[$dir]);
      $all->appendChild($packagesTab[$dir]);
    }

    $totalLines       = 0;
    $totalTestedLines = 0;
    $testedLinesPackage = array();
    $totalLinesPackage  = array();
    foreach ($coverageByFile as $file => $coveragePercent)
    {
      $key = pathinfo($file, PATHINFO_DIRNAME);
      $srcfile = $dom->createElement('srcfile');
      $srcfile->setAttribute('name', $file);
      $numberOfLines = $this->getNumberOfLinesFile(sfConfig::get('sf_root_dir') . $file);
      $testedLines = round(substr($coveragePercent, 0, -1) / 100 * $numberOfLines);
      $totalLines += $numberOfLines;
      $totalTestedLines += $testedLines;
      $testedLinesPackage[$key] += $testedLines;
      $totalLinesPackage[$key] += $numberOfLines;
      $coverageString = sprintf('%s (%s/%s)', $coveragePercent, $testedLines, $numberOfLines);

      $coverageClass = $dom->createElement('coverage');
      $coverageClass->setAttribute('type', 'class, %');
      $coverageClass->setAttribute('value', '0% (0/0)');
      $srcfile->appendChild($coverageClass);

      $coverageMethod = $dom->createElement('coverage');
      $coverageMethod->setAttribute('type', 'method, %');
      $coverageMethod->setAttribute('value', '0% (0/0)');
      $srcfile->appendChild($coverageMethod);

      $coverageBlock = $dom->createElement('coverage');
      $coverageBlock->setAttribute('type', 'block, %');
      $coverageBlock->setAttribute('value', '0% (0/0)');
      $srcfile->appendChild($coverageBlock);

      $coverageLine = $dom->createElement('coverage');
      $coverageLine->setAttribute('type', 'line, %');
      $coverageLine->setAttribute('value', $coverageString);
      $srcfile->appendChild($coverageLine);

      $packagesTab[$key]->appendChild($srcfile);
    }

    foreach($dirs as $dir)
    {
      $totalLinesString = sprintf('%s%% (%s/%s)', round($testedLinesPackage[$dir]/$totalLinesPackage[$dir]*100), $testedLinesPackage[$dir], $totalLinesPackage[$dir]);
      $totalLinesPackageElements[$dir]->setAttribute('value', $totalLinesString);
    }

    $stringTotalLines = sprintf('%s%% (%s/%s)', round($totalTestedLines/$totalLines*100), $totalTestedLines, $totalLines);
    $elementTotalLines->setAttribute('value', $stringTotalLines);

    $srcfiles->setAttribute('value', count($coverageByFile));
    $srclines->setAttribute('value', $totalLines);

    return $dom->saveXml();
  }

  protected function getNumberOfLinesFile($file)
  {
    return count(file($file));
  }

  protected function getCoveragePercentByFile()
  {
    $coverage = array();
    $files = sfFinder::type('file')->name('*.php')->in($this->getUnitTestsDir());
    foreach ($files as $file)
    {
      $testFile = substr($file, strlen(sfConfig::get('sf_test_dir')) + 1);
      $testedFile = $this->getTestedFileFromTestFile($file);
      $cmd        = sprintf('%s symfony test:coverage test/%s %s', sfToolkit::getPhpCli(), $testFile, $testedFile);
      $output     = '';
      exec($cmd, $output);
      $percent = substr($output[1], -3);
      $coverage[$testedFile] = $percent;
    }
    return $coverage;
  }

  protected function getRelativePathFromSfRootDir($path)
  {
    return substr($path, strlen($this->getUnitTestsDir()));
  }

  protected function getUnitTestsDir()
  {
    return sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';
  }

  protected function getTestedFileFromTestFile($testFile)
  {
    $basename   = pathinfo($testFile, PATHINFO_BASENAME);
    $testedFile = str_replace(array('Test', '.php'), array('', '.class.php'), $basename);
    $testedPath = str_replace($basename, $testedFile, $this->getRelativePathFromSfRootDir($testFile));
    return $testedPath;
  }

}