<?php require_once INCLUDES.'inc_header.php'; ?>

<!-- Content Row -->
<div class="row">

	<!-- Earnings (Monthly) Card Example -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-primary shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Grupo</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo empty($d->grupo) ? 'Sin grupo asignado' : sprintf('<a href="alumno/grupo">%s</a>', $d->grupo->nombre); ?></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-user-friends fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Earnings (Monthly) Card Example -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Materias</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><a href="alumno/grupo">Ver materias</a></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-book fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Earnings (Monthly) Card Example -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-info shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Lecciones</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><a href="alumno/lecciones">Continuar</a></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-layer-group fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	
	<div class="col-lg-7 mb-4">

		<!-- Anuncios educativos -->
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Anuncio Educativo</h6>
			</div>
			<div class="card-body">
				<p class="mb-0">Fecha: Sábado 19 de Noviembre de 2022</p>
				<p class="mb-0">Hora: 7:00 a 9:00 horas.</p>
				<p class="mb-0">Reunión de Resolución de dudas: .</p>
				<p>Enlace a la videollamada: https://meet.google.com/pph-utsh-sjy</p>
				<img src="<?php echo get_image('inscripciones.jpeg'); ?>" class="img-fluid" style="width: 800px;">
			</div>
		</div>
	</div>
</div>

<?php require_once INCLUDES.'inc_footer.php'; ?>