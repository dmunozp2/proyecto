

<!-- Nav Item - Dashboard -->
<li class="nav-item <?php echo $slug === 'dashboard' ? 'active' :null; ?>">
	<a class="nav-link" href="dashboard">
		<i class="fas fa-fw fa-tachometer-alt"></i>
		<span>Dashboard</span></a>
</li>

<!-- Divider -->
<hr class="sidebar-divider">

<!-- Heading -->
<div class="sidebar-heading">
	Menú
</div>

<!-- Nav Item - Profesores -->
<li class="nav-item <?php echo $slug === 'profesores' ? 'active' :null; ?>">
	<a class="nav-link" href="profesores">
		<i class="fas fa-fw fa-users"></i>
		<span>Profesores</span></a>
</li>

<!-- Nav Item - Alumnos -->
<li class="nav-item <?php echo $slug === 'alumnos' ? 'active' :null; ?>">
	<a class="nav-link" href="alumnos">
		<i class="fas fa-fw fa-book-reader"></i>
		<span>Alumnos</span></a>
</li>

<!-- Nav Item - Materias -->
<li class="nav-item <?php echo $slug === 'materias' ? 'active' :null; ?>">
	<a class="nav-link" href="materias">
		<i class="fas fa-fw fa-book"></i>
		<span>Materias</span></a>
</li>

<!-- Nav Item - Grupos -->
<li class="nav-item <?php echo $slug === 'grupos' ? 'active' :null; ?>">
	<a class="nav-link" href="grupos">
		<i class="fas fa-fw fa-graduation-cap"></i>
		<span>Grupos</span></a>
</li>

<!-- Nav Item - Lecciones -->
<li class="nav-item <?php echo $slug === 'lecciones' ? 'active' :null; ?>">
	<a class="nav-link" href="lecciones">
		<i class="fas fa-fw fa-chalkboard-teacher"></i>
		<span>Lecciones</span></a>
</li>

<!-- Divider -->
<hr class="sidebar-divider d-none d-md-block">

<!-- Sidebar Toggler (Sidebar) -->
<div class="text-center d-none d-md-inline">
	<button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>