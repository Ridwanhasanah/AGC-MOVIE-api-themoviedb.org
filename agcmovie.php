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

	$parameters = array();

	$parameters[] = 'with_genres=18';
	$parameters[] = 'include_adult=false';
	$parameters[] = 'sort_by=popularity.desc';

	$params = '';

	if (!empty($parameters)) {
		$params = implode('&', $parameters);

	}

	$api_key = 'd5dbdf1e3e3de7f364230240dcea83ee';

	$source_url = 'https://api.themoviedb.org/3/discover/movie?api_key={api-key}&{parameters}';
	$source_url = str_replace(array('{api-key}','{parameters}'), array($api_key, $parameters), $source_url);

	$data = json_decode(file_get_contents($source_url) );
	$poster_url_template = 'http://image.tmdb.org/t/p/original{file-name}?w=100';
	echo '<pre>';
	//print_r($data);
	echo '</pre>';
	//gunakan link ini http://i0.wp.com/image.tmdb.org/t/p/original{file-name}?w=100 jika ingin menggunkan jetpack agar lebih cepat

	$total_pages = $data->total_pages;

	$html = '';

	foreach ($data->results as $dt) {
		$id     = $dt->id; //mengambil id di objenct arrray
		$title  = $dt->title; //mengambil title di objenct arrray
		$poster = $dt->poster_path; //mengambil url gambar di objenct arrray

		if (!empty($poster)) {
			$image_url = str_replace('{file-name}', $poster, $poster_url_template);
			$html     .= '<span styel="nargin: 10px;">'.
						 '<a href="'.get_home_url().'movie'.$id.'-'.sanitize_title($title).'">'.
						 '<img src="'.$image_url.'" width="100" height="150" alt="'.$title.'" title="'.$title.'"/>'.
						 '</a></span>';
		}
	}
	
	$html .= '<div style="clear:both"></div>';
	
	return $html;
}

?>