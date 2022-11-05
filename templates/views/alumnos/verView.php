<?php require_once INCLUDES.'inc_header.php'; ?>

<div class="row">
	<div class="col-xl-6 col-md-6 col-12">
		<!-- Collapsable Card Example -->
		<div class="card shadow mb-4">
			<!-- Card Header - Accordion -->
			<a href="#alumnos_data" class="d-block card-header py-3" data-toggle="collapse" 
				role="button" aria-expanded="true" aria-controls="alumnos_data">
				<h6 class="m-0 font-weight-bold text-primary"><?php echo $d->title; ?></h6>
			</a>
			<!-- Card Content - Collapse -->
			<div class="collapse show" id="alumnos_data">
				<div class="card-body">
					<form action="alumnos/post_editar" method="post">
						<?php echo insert_inputs(); ?>
                        <input type="hidden" name="id" value="<?php echo $d->a->id; ?>" required>
						
						<div class="form-group">
							<label for="nombres">Nombre(s)</label>
							<input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $d->a->nombres; ?>" required>
						</div>

						<div class="form-group">
							<label for="apellidos">Apellido(s)</label>
							<input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo $d->a->apellidos; ?>"required>
						</div>
						
						<div class="form-group">
							<label for="email">Correo electrónico</label>
							<input type="email" class="form-control" id="email" name="email" value="<?php echo $d->a->email; ?>"required>
						</div>

						<div class="form-group">
							<label for="telefono">Teléfono</label>
							<input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $d->a->telefono; ?>">
						</div>

						<div class="form-group">
							<label for="password">Constraseña</label>
							<input type="password" class="form-control" id="password" name="password">
						</div>

						<div class="form-group">
							<label for="conf_password">Confirmar constraseña</label>
							<input type="password" class="form-control" id="conf_password" name="conf_password">
						</div>

						<hr>

						<?php if (!empty($d->grupos)): ?>
							<div class="form-group">
								<label for="id_grupo">Grupo</label>
								<select name="id_grupo" id="id_grupo" class="form-control">
								<?php foreach ($d->grupos as $g): ?>
									<?php echo sprintf('<option value="%s" %s>%s</option>', $g->id, $g->id == $d->a->id_grupo ? 'selected' : null, $g->nombre); ?>
									<?php endforeach; ?>
								</select>
							</div>
							<?php else: ?>
						<div class="form-group">
							<label for="id_grupo">Grupo</label>
							<div class="alert alert-danger">No hay grupos registrados.</div>
						</div>
						<?php endif; ?>

						<button class="btn btn-success" type="submit" <?php echo empty($d->grupos) ? 'disabled' : null; ?>>Guardar cambios</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php require_once INCLUDES.'inc_footer.php'; ?>