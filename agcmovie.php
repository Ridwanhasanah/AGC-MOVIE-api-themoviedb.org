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

	$uri = trim($_SERVER['REQUEST_URI'],'/'); //fungdi trim() berguna untuk menghilangkan whitespace (spasi lebih dari satu)
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
	$source_url = str_replace('{page}', $page, $source_url);


	$data = json_decode(file_get_contents($source_url) );
	$poster_url_template = 'http://image.tmdb.org/t/p/original{file-name}?w=100';
	//gunakan link ini http://i0.wp.com/image.tmdb.org/t/p/original{file-name}?w=100 jika ingin menggunkan jetpack agar lebih cepat
	echo '<pre>';
	echo get_home_url().'<br>';
	print_r(esc_url( get_page_link() ));
	echo '</pre>';


	$total_pages = $data->total_pages;

	$html = '';

	foreach ($data->results as $dt) {
		$id     = $dt->id; //mengambil id di objenct arrray
		$title  = $dt->title; //mengambil title di objenct arrray
		$poster = $dt->poster_path; //mengambil url gambar di objenct arrray

		if (!empty($poster)) { //menampilkan gambar poster movie
			$image_url = str_replace('{file-name}', $poster, $poster_url_template); //url gambar
			/*Tampilan gambar*/
			$html     .= '<span styel="margin: 10px;">'.  
						 '<a href="'.get_home_url().'/movie/'.$id.'-'.sanitize_title($title).'">'.
						 '<img style="padding : 5px;" src="'.$image_url.'" width="218" height="200" alt="'.$title.'" title="'.$title.'"/>'.
						 '</a></span>';
		}
		/*echo '<pre>';
		print_r($title);
		echo '</pre>';*/
	}

	$html .= '<div style="clear:both"></div>';

	$pages = array();
	for ($i=1; $i<=23/*$total_pages*/; $i++){  //uncoment variable $total_page jika ingin menampilkan seluruh page yang ada
		if ($i == $page) { //jika page sama dengan page angka page yang tampil maka
			$pages[] = '<a class="active" href="'.get_home_url().'/page/'.$i.'">'.$i.'</a>'; //tamplkan angka page dan berikan warna backgound

		}else {
		$pages[] = '<a href="'.get_home_url().'/page/'.$i.'">'.$i.'</a>';


		}
	}

	if (!empty($pages)) {
		//utntuk menampilkan angka page yang ada dan membrikan link sesuai dengan angka page yang tampil
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




/*======= Virtual Page Detail MOvie Start =========*/
add_filter('the_posts', 'rh_agc_movie_virtual_page' );

function rh_agc_movie_virtual_page($posts){

	global $wp_query;

	if (count($posts) == 0 && !is_admin()) {
		$post = new stdClass;

		$url      = trim($_SERVER['REQUEST_URI'],'/');
		//echo '<h3>'.$url.'</h3>';
		$url      = str_replace('movie-', '', $url);
		$home     = end(explode('/', get_home_url()));
		$url 	  = str_replace( $home, '', $url);
		//echo '<h3>'.$home.'</h3>';
		//echo '<b style="font-size: 20px;">'.$url.'</b>';
		$slug     = end(explode('/', $url));
		$movie_id = array_shift(explode('-', $slug));
		$title    = ucwords(trim(str_replace(array($movie_id, '-'), array('', ' '), $slug ) ) );

		$post->ID             = -999;
		$post->post_title     = $title;
		$post->post_content   = rh_agc_movie_detail($movie_id);
		$post->post_author    = 1;
		$post->comment_status = 'closed';
		$post->comment_count  = 0;
		$post->post_type      = 'page';
		$post->post_name      = $url;

		$posts = array($post);

		$wp_query->is_page             = TRUE;
		$wp_query->is_singular         = TRUE;
		$wp_query->is_home             = FALSE;
		$wp_query->is_archive          = FALSE;
		$wp_query->is_category 		   = FALSE;
		unset($wp_query->query['error']);
		$wp_query->query_vars['error'] = '';
		$wp_query->is_404              = FALSE;

	}
	return $posts;
}


function rh_agc_movie_detail($movie_id){

	$parameters   = array();
	$parameters[] = 'append_to_response=images,trailers,similar_movies';
	$parameters[] = 'include_adult=false';
	$parameters[] = 'sort_by=popularity.desc';

	$params       = '';

	if (!empty($parameters)) {
		
		$params = implode('&', $parameters);
	}

	$api_key    = 'd5dbdf1e3e3de7f364230240dcea83ee';
	$source_url = 'https://api.themoviedb.org/3/movie/{movie-id}?api_key={api-key}&{parameters}';
	$source_url = str_replace(array('{movie-id}','{api-key}','{parameters}'), array($movie_id, $api_key, $params), $source_url);


	$data                  = json_decode(file_get_contents($source_url) );
	$poster_url_template   = 'http://image.tmdb.org/t/p/original{file-name}?w=100';
	$backdrop_url_template = 'http://image.tmdb.org/t/p/original{file-name}?w=500';

	shuffle($data->images->backdrops);
	$backdrop = $data->images->backdrops[0]->file_path;
	$genre    = $data->genres[0]->name;
	$homepage = $data->homepage;
	$overview = $data->overview;

	shuffle($data->images->posters);
	$poster  = $data->images->posters[0]->file_path;
	$release  = $data->release_date;
	$runtime  = $data->runtime;
	$spoken   = $data->spoken_languages[0]->name;
	$tagline  = $data->tagline;
	$title    = $data->title;

	shuffle($data->trailers->youtube);
	$trailer     = $data->trailers->youtube[0]->source;
	$similar     = $data->similar_movies->results;
	$total_pages = $data->similar_movies->total_pages;
	$poster      = str_replace('{file-name}', $poster, $poster_url_template);
	$backdrop    = str_replace('{file-name}', $backdrop, $backdrop_url_template);

	$html = '<figure><img src="'.$backdrop.'"width="100%"/></figure>'.
			'<h2>'.$tagline.'</h2>'.
			'<p>'.$overview.'</p>'.
			'<div class="video_container">'.
			'<iframe width="100%" height="315" src="https://www.youtube.com/embed/'.
			$trailer.'"frameborder="0" allowfullscreen></iframe></div>'.
			'<p><img style="padding-top: 10px;" src="'.$poster.'" class="alignleft" />'.
			'Title : '.$title.'<br>'.
			'Genre : '.$genre.'<br>'.
			'Language : '.$spoken.'<br>'.
			'Release : '.$release.'<br>'.
			'Homeage : '.$homepage.'<br>'.
			'</p><div style="clear:both;"></div>';

	$html .= '<h2>Similar Movie</h2>';

	foreach ($similar as $sim) {
		if (!empty($sim->poster_path)) {
			
			$image_url = str_replace('{file-name}', $sim->poster_path, $poster_url_template);
			$html .= '<span style="margin:10px">'.
					 '<a href="'.get_home_url().'/'.$sim->id.'-'.
					 sanitize_title($sim->title ).'"><img style="margin:5px;" src="'.$image_url.
					 '"width="150" height="150" alt="'.$sim->title.'" title="'.$sim->title.'"/></a></span>'; 
		}
	}

	$html .= '<div style="clear:both;"></div>';

	return $html;
}

/*======= Virtual Page Detail MOvie End =========*/

?>