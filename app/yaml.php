<?php 
/**
 * интерфейс yaml я каталога
 */
class yaml
{
  private $catalog = null;
  private $xml;
  private $items = array();
  
  function __construct($xml)
  {
    $this->xml = $xml;
  }

  private function buildCatalog($built = null)
  {
    if (is_null($built)) {
      $count = count($this->xml->shop->categories->category);
      $build = array();
      // echo $count . "<br>";
      foreach ($this->xml->shop->categories->category as $category) {
        $attr = $category->attributes();
        if ((int)$attr->parentId == 0) {
          $count --;
          $build[(int)$attr->id] = (string)$category;
        }
      }
      return $this->buildCatalog($build);
    } else {
      $count = count($this->xml->shop->categories->category) - count($built);
      $build = $built;
      foreach ($this->xml->shop->categories->category as $category) {
        $attr =$category->attributes();
        if (isset($built[(int)$attr->parentId])) {
          $count --;
          unset($build[(int)$attr->parentId]);
          $build[(int)$attr->id] = $built[(int)$attr->parentId] . " → " .(string)$category;
        }
      }

      if ($count == count($this->xml->shop->categories->category) - count($built)) {
        return $build;
      }
    }
      return $this->buildCatalog($build);

    
  }

  public function getCatalog()
  {
    if (is_null($this->catalog)) {
      $this->catalog = $this->buildCatalog();
    }

    return $this->catalog;
  }

  /**
   * interface for yaml goods
   * @return [type] [description]
   */
  public function getGoods() {
    $search_str = 'https://xn-----6kcavojtahc9abe5aii1g0he.xn--p1ai/';
    $replac_str = '/var/www/u7837304/data/www/zapchasti-dlya-kolyasok.rf/';

    $result = array();
    $cat = $this->buildCatalog();
    foreach ($this->xml->shop->offers->offer as $offer) {
      $picture = explode("?", (string)$offer->picture);
      $picture    = str_replace($search_str, $replac_str, $picture[0]);
      $attr = $offer->attributes();
      $url = trim((string)$offer->url);
      if (strlen($url) > 319) {
        $url = 0;
      }
      $name = trim((string)$offer->name . " [".(string)$offer->vendorCode."]");
      if (mb_strlen($name) > 99) {
        $name = mb_substr($name, 0, 99);
      }
      if ((string)$attr->available == "true") {
        $deleted = 0;
      } else {
        $deleted = 1;
      }

      $description = filter_var(trim((string)$offer->description), FILTER_SANITIZE_STRING);
      $description = str_replace("&nbsp;", " ", $description);
      $description = str_replace("&#8381;", "руб.", $description);
      $description .= "\nподробная информация о товаре на нашем сайте: $url";

      $result[] = array(
        'album'         => trim($cat[(int)$offer->categoryId]),
        'name'          => $name,
        'description'   => $description,
        'price'         => trim((float)$offer->price),
        'deleted'       => $deleted,
        'url'           => $url,
        'picture_path'  => trim(str_replace("/styles/600x450/public", "", $picture))
      );
    }

    return $result;
  }



}