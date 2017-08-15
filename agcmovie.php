<?php
/*
  Plugin Name: AGC Movie
  Plugin URI: https://www.facebook.com/ridwan.hasanah3
  Description: Auto Generate Content Movie
  Version: 1.0
  Author: Ridwan Hasanah
  Author URI: https://www.facebook.com/ridwan.hasanah3
*/

add_shortcode('agc-movie', 'rh_agc_movie_index' );

function rh_agc_movie_index(){

	$uri = trim($_SERVER['REQUEST_URI'],'/');
	$page = 1;
	if (strpos($uri, 'page/') !== false) {
		
		$page = end(explode('/', $uri));

	}

	$parameters = array();

	$parameters[] = 'with_genres=18';
	$parameters[] = 'primary_relese_year=2017';
	$parameters[] = 'page={page}';
	$parameters[] = 'include_adult=false';
	$parameters[] = 'sort_by=popularity.desc';

	$params = '';

	if (!empty($parameters)) {
		$params = implode('&', $parameters);

	}

	$api_key = 'd5dbdf1e3e3de7f364230240dcea83ee';

	$source_url = 'https://api.themoviedb.org/3/discover/movie?api_key={api-key}&{parameters}';
	$source_url = str_replace(array('{api-key}','{parameters}'), array($api_key, $params), $source_url);

	$data = json_decode(file_get_contents($source_url) );
	$poster_url_template = 'http://image.tmdb.org/t/p/original{file-name}?w=100';
	//gunakan link ini http://i0.wp.com/image.tmdb.org/t/p/original{file-name}?w=100 jika ingin menggunkan jetpack agar lebih cepat
	echo '<pre>';
	//print_r($data);
	echo '</pre>';

	$total_pages = $data->total_pages;

	$html = '';

	foreach ($data->results as $dt) {
		$id     = $dt->id; //mengambil id di objenct arrray
		$title  = $dt->title; //mengambil title di objenct arrray
		$poster = $dt->poster_path; //mengambil url gambar di objenct arrray

		if (!empty($poster)) {
			$image_url = str_replace('{file-name}', $poster, $poster_url_template);
			$html     .= '<span styel="nargin: 10px;">'.
						 '<a href="'.get_home_url().'/movie-'.$id.'-'.sanitize_title($title).'">'.
						 '<img src="'.$image_url.'" width="100" height="150" alt="'.$title.'" title="'.$title.'"/>'.
						 '</a></span>';
		}
	}

	$html .= '<div style="clear:both"></div>';

	$pages = array();
	for ($i=1; $i<=$total_pages; $i++){
		if ($i == $page) {
			$pages[] = '<a class="active" href="'.get_home_url().'/page/'.$i.'">'.$i.'</a>';

		}else {
		$pages[] = '<a href="'.get_home_url().'/page/'.$i.'">'.$i.'</a>';


		}
	}

	if (!empty($pages)) {
		$nav = '<div class="rh-pagination">'.implode('', $pages).'</div>';
		$html .= '<hr/>'.$nav;
	}
	
	return $html;
}


/*======= CSS Include Start =========*/
add_action('wp_head', 'rh_agc_movie_pagination_style' );
function rh_agc_movie_pagination_style(){

	echo '<link href="'.plugin_dir_url(__FILE__ ).'style.css" rel="stylesheet" type="text/css">';
}
/*======= CSS Include End =========*/
?>