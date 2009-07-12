<?php
abstract class infosProviderSerieBase extends infosProviderBase
{
  /**
   * Renvoi un tableau contenant la liste des séries possibles pour le nom donné
   *
   * @param  string             $serie
   * @return array[int]=>string liste des series
   * @throws SerieNonFoundException
   */
  abstract public function getSeries($serie);

  /**
   * Trouve le titre d'in épisode selon la série, la saison et l'épisode
   *
   * @param  string $serie   résultat de la méthode getSeries
   * @param  int    $saison
   * @param  int    $episode
   * @return string nom de l'épisode selon la série, la saison et l'épisode
   * @throws EpisodeNonFoundException
   * @throws SeasonNonFoundException
   */
  abstract public function getEpisode($serie, $saison, $episode);

}