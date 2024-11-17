<?php
include_once './cfg.php';

function showPage($alias)
{
  global $link;
  $alias_clear = htmlspecialchars($alias);
  $query = "SELECT * FROM page_list WHERE alias = '$alias_clear' LIMIT 1";
  $result = mysqli_query($link, $query);
  $row = mysqli_fetch_array($result);

  if (empty($row['id'])) {
    $content = "Sorry, page not found";
    $title = "Page not found";
    $heading = "Page not found";
  } else {
    $content = $row["page_content"];
    $title = $row["page_title"];
    $heading = $row["page_heading"];
  }

  return array($title, $heading, $content);
}
