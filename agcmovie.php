<?php
/*
  Plugin Name: AGC Movie
  Plugin URI: https://www.facebook.com/ridwan.hasanah3
  Description: Auto Generate Content Movie
  Version: 1.0
  Author: Ridwan Hasanah
  Author URI: https://www.facebook.com/ridwan.hasanah3
*/
use phpFastCache\CacheManager;
//my apikey d5dbdf1e3e3de7f364230240dcea83ee

add_shortcode('agc-movie', 'rh_agc_movie_index' );

function rh_agc_movie_index(){

	$uri = trim($_SERVER['REQUEST_URI'],'/'); //fungdi trim() berguna untuk menghilangkan whitespace (spasi lebih dari satu)
	$page = 1;
	if (strpos($uri, 'page/') !== false) {
		
		$page = end(explode('/', $uri));

	}

	$options = get_option("rh-agc-movie-parameter" );
	extract($options);

	$parameters = array();

	$parameters[] = 'with_genres='.$genre;
	$parameters[] = 'primary_relese_year='.$year;
	$parameters[] = 'page={page}';
	$parameters[] = 'include_adult='.$adult;
	$parameters[] = 'sort_by='.$sortby;

	$params = '';

	if (!empty($parameters)) {
		$params = implode('&', $parameters);

	}

	$api_key = $apikey;

	$source_url = 'https://api.themoviedb.org/3/discover/movie?api_key={api-key}&{parameters}';
	$source_url = str_replace(array('{api-key}','{parameters}'), array($api_key, $params), $source_url);
	$source_url = str_replace('{page}', $page, $source_url);


	$data = json_decode(file_get_contents($source_url) );
	$poster_url_template = 'http://image.tmdb.org/t/p/original{file-name}?w=100';
	//gunakan link ini http://i0.wp.com/image.tmdb.org/t/p/original{file-name}?w=100 jika ingin menggunkan jetpack agar lebih cepat
	/*echo '<pre>';
	echo get_home_url().'<br>';
	print_r(esc_url( get_page_link() ));
	echo '</pre>';*/


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

	$options = get_option("rh-agc-movie-parameter" );
	extract($options);

	$parameters   = array();
	$parameters[] = 'append_to_response=images,trailers,similar_movies';
	$parameters[] = 'include_adult='.$adult;
	$parameters[] = 'sort_by='.$sortby;

	$params       = '';

	if (!empty($parameters)) {
		
		$params = implode('&', $parameters);
	}

	$api_key    = $apikey;//'d5dbdf1e3e3de7f364230240dcea83ee';
	$source_url = 'https://api.themoviedb.org/3/movie/{movie-id}?api_key={api-key}&{parameters}';
	$source_url = str_replace(array('{movie-id}','{api-key}','{parameters}'), array($movie_id, $api_key, $params), $source_url);

/*===============================================================================================================================*/

	$dir = dirname(__FILE__);
	$cache_id = md5($movie_id.$params);

	$options = get_option('rh-agc-movie-cache' );
	if (!is_array($options)) {
		
		$options = array(
					'cache'=>'false',
					'cachetype'=>'textfile');
	}

	extract($options);

	if (($cache=='true')&&($cachetype=='textfile')) {
		
		$cache_file = "$dir/cache/$cache_id.txt";
		if (!file_exists($cache_file)) {
			if (!file_exists("$dir/cache")) {
				if (!is_writable($dir)) {
					chmod($dit, 0755);
				}

				mkdir("$dir/cache",0755);
			}

			$data = file_get_contents($source_url);
			file_put_contents($cache_file, $data);
		}

		$data = json_decode(file_get_contents($cache_file));

	}elseif (($cache == 'true')&&($cache_file=='phpfastcache')) {
		
		require_once dirname(__FILE__).'/phpfastcache/src/autoload.php';
		CacheManager::setup(array( 
			'path'=>dirname(__FILE__),
			'securityKey'=>'cache') );

		$cache  = CacheManager::getInstance('sqlite');
		$result = $cache->getItem($cache_id);
		if (empty($result->get())) {
			
			$data = file_get_contents($source_url);
			$result->set($data)->expiresAfter(86400);
			$cache->save($result);
			$result = $cache->getItem($cache_id);

		}

		$data = json_decode($result->get());
	}else{

		$data = json_decode(file_get_contents($source_url) );

	}
	
	$poster_url_template   = 'http://image.tmdb.org/t/p/original{file-name}?w=100';
	$backdrop_url_template = 'http://image.tmdb.org/t/p/original{file-name}?w=500';

	shuffle($data->images->backdrops);
	$backdrop = $data->images->backdrops[0]->file_path;
	$genre    = $data->genres[0]->name;
	$homepage = $data->homepage;
	$overview = $data->overview;

	shuffle($data->images->posters);
	$poster   = $data->images->posters[0]->file_path;
	$release  = $data->release_date;
	$year     = array_shift(explode('-', $release));
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

/*================================================================================================================================*/

	$the_similar = '';
	foreach ($similar as $im) {
		if (!empty($sim->poster_path)) {
			$image_url = str_replace('{file-name', $sim->poster_path, $poster_url_template);
			$the_similar .= '<span style="margin:10px;">'.
						    '<a href="'.get_home_url().'/'.$sim->id.'-'.sanitize_title($sim->title).'">'.
						    '<img src="'.$image_url.'"width="100 height=150" alt="'.$sim->title.'" title="'.$sim->title.'"/>'.
						    '</a></span>';
		}
	}

	$the_similar .= '<div style="clear:both"></div>';

	$options = get_option("rh-agc-movie-template" );
	extract($options);

	if ($template) {
		$template = '<figure><img src="{backdrop}"></figure><p>{overview}</p>{similar}';
	}

	/*$html = '<figure><img src="'.$backdrop.'"width="100%"/></figure>'.
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

	$html .= '<div style="clear:both;"></div>';*/

	$html = str_replace(array(
							'{title}', 
							'{tagline}', 
							'{overview}',
							'{trailer}',
							'{poster}',
							'{backdrop}',
							'{genre}',
							'{spoken}', 
							'{homepage}', 
							'{similar}', 
							'{release}', 
							'{year}'),
						array(
							$title, 
							$tagline,  
							$overview, 
							$trailer, 
							$poster, 
							$backdrop, 
							$genre,
							$spoken, 
							$homepage, 
							$the_similar, 
							$release, 
							$year),
						html_entity_decode($template) );

	return $html;
}

/*======= Virtual Page Detail MOvie End =========*/



/*======= MENU & OPTIONS START  =========*/
	/*======= MENU START  =========*/

add_action('admin_menu','rh_agc_moive_menu');

function rh_agc_moive_menu(){

	add_menu_page(
		'AGC MOVIE', 
		'AGC MOVIE', 
		'manage_options', 
		'agc-movie', 
		'rh_agc_movie_options',
		plugin_dir_url(__FILE__).'movie-icon.png');

	add_submenu_page(
		'agc-movie',
		'Api Parameter',
		'Api Parameter',
		'manage_options',
		'agc-movie',
		'rh_agc_movie_options' );

	add_submenu_page(  //submenu untk template
		'agc-movie',
		'Temaplate',
		'Temaplate',
		'manage_options',
		'agc-movie-template',
		'rh_agc_movie_template_options' );

	add_submenu_page(
		'agc-movie',
		'Cache',
		'Cache',
		'manage_options',
		'agc-movie-cache',
		'rh_agc_movie_cache_options' );

}
	/*======= MENU End  =========*/

	/*======= OPTIONS START  =========*/

function rh_agc_movie_options(){

	echo '<h2>AGC MOVIE</h2>';

	if ($_POST['rh-agc-movie-parameter-submit']) {

		$options['apikey'] = $_POST['apikey'];
		$options['year']   = $_POST['year'];
		$options['genre']  = $_POST['genre'];
		$options['adult']  = $_POST['adult'];
		$options['sortby'] = $_POST['sortby'];

		update_option("rh-agc-movie-parameter",$options);

		echo '<div class="updated"><p><strong>Options Saved.</strong></p></div>';
	}	


		$options = get_option("rh-agc-movie-parameter");
		extract($options);

		?>
			<form method="post">
				<table>
					<tr>
						<td><label for="api-key">API KEY</label></td>
						<td><input type="text" size="40" name="apikey" id="apikey" value="<?php echo $apikey; ?>"></td>
					</tr>
					<tr>
						<td><label for="year">YEAR</label></td>
						<td><input type="text" name="year" id="year" size="5" value="<?php echo $year; ?>"></td>
					</tr>
					<tr>
						<td><label for="genre">GENRE</label></td>
						<td>
							<select id="genre" name="genre">
								<optgroup>
									<option value="">SELECT</option>
								</optgroup>
								<optgroup label="----------------------------">
									<option value="28" <?php if($genre==28) echo 'SELECTED'; ?>>Action</option>
									<option value="12" <?php if($genre==12) echo 'SELECTED'; ?>>Adventure</option>
									<option value="16" <?php if($genre==16) echo 'SELECTED'; ?>>Animation</option>
									<option value="35" <?php if($genre==35) echo 'SELECTED'; ?>>Comedy</option>
									<option value="80" <?php if($genre==80) echo 'SELECTED'; ?>>Crime</option>
									<option value="18" <?php if($genre==18) echo 'SELECTED'; ?>>Drama</option>
									<option value="10751" <?php if($genre==10751) echo 'SELECTED'; ?>>Family</option>
									<option value="14" <?php if($genre==14) echo 'SELECTED'; ?>>Fantasy</option>
									<option value="36" <?php if($genre==36) echo 'SELECTED'; ?>>History</option>
									<option value="27" <?php if($genre==27) echo 'SELECTED'; ?>>Horror</option>
									<option value="10402" <?php if($genre==10402) echo 'SELECTED'; ?>>Music</option>
									<option value="9648" <?php if($genre==9648) echo 'SELECTED'; ?>>Mystery</option>
									<option value="10749" <?php if($genre==10749) echo 'SELECTED'; ?>>Romance</option>
									<option value="878" <?php if($genre==878) echo 'SELECTED'; ?>>Scince Fiction</option>
									<option value="10770" <?php if($genre==10770) echo 'SELECTED'; ?>>TV Movie</option>
									<option value="53" <?php if($genre==53) echo 'SELECTED'; ?>>Thriller</option>
									<option value="10752" <?php if($genre==10752) echo 'SELECTED'; ?>>WAR</option>
									<option value="37" <?php if($genre==37) echo 'SELECTED'; ?>>Western</option>
								</optgroup>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="adul">Adult ?</label></td>
						<td>
							<select id="adult" name="adult">
								<option value="false" <?php if($adult==false) echo 'SELECTED'?>>NO</option>
								<option value="true" <?php if($adult==true) echo 'SELECTED'?>>YES</option>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="sortby">Sort by</label></td>
						<td>
							<select id="sortby" name="sortby">
								<option value="popularity.desc" <?php if($sortby=='popularity.desc') echo 'SELECTED';?>>
									Popularity
								</option>
								<option value="revenue.desc" <?php if($sortby=='revenue.desc') echo 'SELECTED';?>>
									Revenue
								</option>
								<option value="release_date.desc" <?php if($sortby=='release_date.desc') echo 'SELECTED';?>>
									Release Date
								</option>
								<option value="vote_average.desc" <?php if($sortby=='vote_average.desc') echo 'SELECTED';?>>
									Vote Average
								</option>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" name="rh-agc-movie-parameter-submit" id="rh-agc-movie-parameter-submit" value="Save" class="button"></td>
					</tr>
				</table>
			</form>
		<?php
	
}

/* Function Template*/
function rh_agc_movie_template_options(){

	echo '<h2>AGC Movie</h2>';

	if ($_POST['rh-agc-movie-template-submit']) {
		
		$options['template'] = htmlentities(stripcslashes($_POST['template'] ) );
		update_option("rh-agc-movie-template",$options );
		echo '<div class="updated"><p><b>Updated Saved.</b></p></div>';

	}

	$options = get_option("rh-agc-movie-template" );
	extract($options);

	if (empty($template)) {
		$template = '<figure><img src="{backdrop}"></figure><p>{overview}</p>{similiar}';
	}
	?>
	<form method="post">
		<label for="template">Template</label><br><br>
		{title}, {tagline}, {overview}, {trailer},  {poster}, {backdrop}, {genre}, {spoken}, {homepage}, {similar}, {release}, {year}
		<br>
		<textarea cols="100" rows="15" id="template" name="template"><?php echo html_entity_decode($template); ?></textarea><br>
		<input type="submit" name="rh-agc-movie-template-submit" name="rh-agc-movie-template-submit" class="button" value="Save">
	</form>
	<?php
}


/* Function Cache*/

function rh_agc_movie_cache_options(){

	echo '<h2>AGC Movie</h2>';

	if ($_POST['rh-agc-movie-cache-submit']) {
		
		$options['cache'] = $_POST['cache'];
		$options['cachetype'] = $_POST['cachetype'];

		update_option('rh-agc-movie-cache', $options );
		echo '<div class="updated"><p><b>Updated Saved.</b></p></div>';


	}

	$options = get_option('rh-agc-movie-cache');

	if (!is_array($options)) {
		
		$options = array(
					'cache'=>'false',
					'cachetype'=>'textfile');
	}

	extract($options);

	?>
	<form method="post">
		<table>
			<tr>
				<td><label for="cache">Save Cache ?</label></td>
				<td>
					<select id="cache" name="cache">
						<option value="true" <?php if($cache == 'true')echo 'SELECTED'; ?>>YES</option>
						<option value="fasle" <?php if($cache == 'false')echo 'SELECTED'; ?>>NO</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="cachetype">Cache Type</label></td>
				<td>
					<select id="cachetype" name="cachetype">
						<option value="textfile" <?php if($cachetype == 'textfile') echo 'SELECTED'; ?>>Text File</option>
						<option value="phpfastcache" <?php if($cachetype == 'phpfastcache') echo 'SELECTED'; ?>>PHP Fast Cache</option>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="submit" name="rh-agc-movie-cache-submit" id="rh-agc-movie-cache-submit" class="button" value="Save">
				</td>
			</tr>
		</table>
	</form>
	<?php
}

	/*======= OPTIONS End  =========*/

/*======= MENU & OPTIONS END  =========*/

?>