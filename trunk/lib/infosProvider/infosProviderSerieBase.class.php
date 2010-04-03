<?php
abstract class infosProviderSerieBase extends infosProviderBase
{
  /**
   * Renvoi un tableau contenant la liste des séries possibles pour le nom donné
   *
   * @param string $serie série recherchée
   *
   * @return array[int]=>string liste des series
   * @throws SerieNonFoundException
   */
  abstract public function getSeries($serie);

  /**
   * Trouve le titre d'un épisode selon la série, la saison et l'épisode
   *
   * @param string $serie   résultat de la méthode getSeries
   * @param int    $saison  numéro de saison
   * @param int    $episode numéro de l'épisode
   *
   * @return string nom de l'épisode selon la série, la saison et l'épisode
   *
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
   * @param string $serie nom de la série à nettoyer
   *
   * @return string nom nettoyé
   */
  public function nettoyerNomSerie($serie)
  {
    return $serie;
  }

  /**
   * Trie les résultats de la recherche de séries par pertinence
   *
   * Trie par ordre alphabétique les résultats, et si le nom exact de la série
   * recherché est dans les résultats, on le place en premier.
   *
   *
   * @param array  $series tableau des séries trouvées
   * @param string $serie  nom de la série recherchée
   *
   * @return array
   */
  protected function sortByRelevance($series, $serie)
  {
    $lowerCase = array_map('strtolower', $series);
    $pos       = array_search(strtolower($serie), $lowerCase, true);
    if ($pos === 0  || $pos =! false)
    {
      $first = $series[$pos];
      unset($series[$pos]);
      $series = array_values($series);
    }
    sort($series);
    if (isset($first))
    {
      $series = array_merge(array($first), $series);
    }
    return $series;
  }
}