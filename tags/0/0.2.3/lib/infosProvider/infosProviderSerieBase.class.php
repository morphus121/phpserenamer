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

  /**
   * Parfois le nom de la série peut être, par exemple pour l'imdn du type
   * "nomSerie" (date), il doit être gardé pour permettre de les différencier,
   * mais préférable de garder seulement la partie, intéressante, c'est à dire,
   * simplement le nom, cette méthode permet de faire cela.
   * Il n'est pas indispensable de la redéfinir, si un cas similaire ne se présente
   * pas, car la méthode renvera alors le même nom que celui passé en paramètre.
   *
   * @return string nom nettoyé
   */
  public function nettoyerNomSerie($serie)
  {
    return $serie;
  }
}