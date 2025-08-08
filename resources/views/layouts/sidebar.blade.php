<div class="pb-3 sidebar pe-4">
    <nav class="navbar bg-light navbar-light">
        <a href="{{ url('/') }}" class="mx-4 mb-3 navbar-brand">
            {{-- <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i>MaxFit</h3> --}}
            <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid" alt="">
        </a>
        <div class="mb-4 d-flex align-items-center ms-4">
            <div class="position-relative">
                <img class="rounded-circle" src="{{ Auth::user()->image }}" alt=""
                    style="width: 40px; height: 40px;">
                <div
                    class="bottom-0 p-1 border border-2 border-white bg-success rounded-circle position-absolute end-0">
                </div>
            </div>
            <div class="ms-3">
                <h6 class="mb-0 text-truncate" style="max-width: 150px;">{{ Auth::user()->name }}</h6>
                <span>Admin</span>
            </div>
        </div>
        <div class="navbar-nav w-100 sidebar-small">
            <a href="{{ url('/') }}" class="nav-item nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                <i class="fa fa-tachometer-alt me-2"></i>Dashboard
            </a>

            <a href="{{ route('users.index') }}" class="nav-item nav-link">
                <i class="fa fa-users me-2"></i>Users
            </a>

            <a href="#" class="nav-item nav-link">
                <i class="fa fa-users me-2"></i>Branch
            </a>

            <a href="#" class="nav-item nav-link">
                <i class="fa fa-users me-2"></i>Coaches
            </a>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                        class="far fa-file-alt me-2"></i>Organisations</a>
                <div class="bg-transparent border-0 dropdown-menu">
                    <a href="{{ route('organisation-types.index') }}" class="nav-item nav-link">
                        <i class="fa fa-sitemap me-2"></i>Org. Types
                    </a>

                    <a href="{{ route('organisations.index') }}" class="nav-item nav-link">
                        <i class="fa fa-building me-2"></i>Organisations
                    </a>
                </div>
            </div>

            <a href="{{ route('medical-assessment-questions.index') }}" class="nav-item nav-link">
                <i class="fa fa-notes-medical me-2"></i>Medical Assessment Ques.
            </a>

            <a href="{{ route('plan-questions.index') }}" class="nav-item nav-link">
                <i class="fa fa-question-circle me-2"></i>Plan Questions
            </a>

            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                        class="far fa-file-alt me-2"></i>Exercise</a>
                <div class="bg-transparent border-0 dropdown-menu">
                    <a href="{{ route('exercises.index') }}" class="nav-item nav-link">
                        <i class="fa fa-dumbbell me-2"></i>Exercises
                    </a>
                    <a href="{{ route('exercise-categories.index') }}" class="nav-item nav-link">
                        <i class="fa fa-th-list me-2"></i>Exercise Categories
                    </a>
                </div>
            </div>

            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                        class="far fa-file-alt me-2"></i>Competitions</a>
                <div class="bg-transparent border-0 dropdown-menu">
                    <a class="nav-item nav-link" href="{{ route('competitions.index') }}" class="dropdown-item"> <i
                            class="fa fa-money-check-alt me-3"></i> Competitions</a>
                    <a class="nav-item nav-link" href="{{ route('competitions.videos') }}" class="dropdown-item"> <i
                            class="fa fa-money-check-alt me-3"></i> Videos</a>
                    <a class="nav-item nav-link" href="#" class="dropdown-item"> <i
                            class="fa fa-money-check-alt me-3"></i> Results</a>
                    <a class="nav-item nav-link" href="#" class="dropdown-item"> <i
                            class="fa fa-money-check-alt me-3"></i> Scores & Rankings</a>
                    <a class="nav-item nav-link" href="{{ route('competitions.appeals') }}" class="nav-item nav-link">
                        <i class="fa fa-money-check-alt me-3"></i>Appeals
                    </a>

                    <a href="{{ route('results.index') }}" class="nav-item nav-link">
                        <i class="fa fa-money-check-alt me-3"></i>Results
                    </a>
                </div>
            </div>

            <a href="{{ route('plans.index') }}" class="nav-item nav-link">
                <i class="fa fa-money-check-alt me-2"></i>Plans
            </a>

            <a href="{{ route('rulesof-counting.index') }}" class="nav-item nav-link">
                <i class="fa fa-money-check-alt me-2"></i>Rules of Counting
            </a>

            {{-- <a href="table.html" class="nav-item nav-link"><i class="fa fa-table me-2"></i>Tables</a>
            <a href="chart.html" class="nav-item nav-link"><i class="fa fa-chart-bar me-2"></i>Charts</a>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                        class="far fa-file-alt me-2"></i>Pages</a>
                <div class="bg-transparent border-0 dropdown-menu">
                    <a href="signin.html" class="dropdown-item">Sign In</a>
                    <a href="signup.html" class="dropdown-item">Sign Up</a>
                    <a href="404.html" class="dropdown-item">404 Error</a>
                    <a href="blank.html" class="dropdown-item">Blank Page</a>
                </div>
            </div> --}}
        </div>
    </nav>
</div>
