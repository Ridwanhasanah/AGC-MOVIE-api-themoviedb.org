Cara Menggunakan Plugin
Plugin ini adalah plugin untuk menampilkan Film Movie Menggunakan API themoviedb.org
-untuk menggunakannya Aktivkan Plugin ini.
-Buat Page baru lalu tambahkan shorcode [agc-movie] lalu save.
-Jadikan page baru tadi sebagai front page tampilan awal (Home Page).
-Masuklah ke menu agc movie lalu masukan apikey dan pilih konten yang akan di tampilkan
berdasarkan Year-Genre-Adult-Sortby lalu save.
-Masuklah ke Submenu Template dan Buatlah sebuat templte untuk menmpilkan Details Movie
yang akan di tampilkan.
contoh 

<h2>{title} {year}-{genre}Movie</h2>
<figure><img src="{backdrop}" width="100%"></figure>
<h2>{tagline}</h2>
<p><img src="{poster}" class="alignleft"/>{overview}</p>
<iframe width="100%" heght="315" src="https;//www.youtube.com/embed/{trailer}" frameborder="0" allowfullscreen></iframe>
{similar}

-Anda juga bisa mengaktivkan Cache atau tidak di bagian Submenu Cache, jika ingin
mengaktivkan cukup pilih YES lalu save.