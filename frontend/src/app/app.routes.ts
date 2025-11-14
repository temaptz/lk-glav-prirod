import { Routes } from '@angular/router';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from './services/auth.service';

const authGuard = () => {
  const auth = inject(AuthService);
  const router = inject(Router);
  
  if (auth.isLoggedIn()) {
    return true;
  }
  
  return router.createUrlTree(['/login']);
};

const adminGuard = () => {
  const auth = inject(AuthService);
  const router = inject(Router);
  
  if (auth.isAdmin()) {
    return true;
  }
  
  return router.createUrlTree(['/home']);
};

const staffGuard = () => {
  const auth = inject(AuthService);
  const router = inject(Router);
  
  // Для менеджеров, специалистов и админов
  if (auth.isAdmin() || auth.isManager() || auth.isSpecialist()) {
    return true;
  }
  
  return router.createUrlTree(['/home']);
};

const rootGuard = () => {
  const auth = inject(AuthService);
  const router = inject(Router);
  
  if (auth.isLoggedIn()) {
    return router.createUrlTree(['/home']);
  }
  
  return router.createUrlTree(['/login']);
};

export const routes: Routes = [
  { path: 'login', loadComponent: () => import('./features/auth/login.component').then(m => m.LoginComponent) },
  {
    path: 'home',
    canActivate: [authGuard],
    loadChildren: () => import('./features/home/home.routes').then(m => m.routes)
  },
  
  // Список клиентов - только для внутреннего персонала
  {
    path: 'clients',
    canActivate: [staffGuard],
    loadComponent: () => import('./features/clients/clients.component').then(m => m.ClientsComponent)
  },
  
  // Общие разделы для всех авторизованных
  {
    path: 'requirements',
    canActivate: [authGuard],
    loadComponent: () => import('./features/requirements/requirements.component').then(m => m.RequirementsComponent)
  },
  {
    path: 'calendar',
    canActivate: [authGuard],
    loadComponent: () => import('./features/calendar/calendar.component').then(m => m.CalendarComponent)
  },
  {
    path: 'artifacts',
    canActivate: [authGuard],
    loadComponent: () => import('./features/artifacts/artifacts.component').then(m => m.ArtifactsComponent)
  },
  {
    path: 'risks',
    canActivate: [authGuard],
    loadComponent: () => import('./features/risks/risks.component').then(m => m.RisksComponent)
  },
  {
    path: 'finance',
    canActivate: [authGuard],
    loadComponent: () => import('./features/finance/finance.component').then(m => m.FinanceComponent)
  },
  
  // Админ-панель - только для admin
  {
    path: 'admin/users',
    canActivate: [adminGuard],
    loadComponent: () => import('./features/admin/users/users.component').then(m => m.UsersComponent)
  },
  {
    path: 'admin/requirements-catalog',
    canActivate: [adminGuard],
    loadComponent: () => import('./features/admin/requirements-catalog/requirements-catalog.component').then(m => m.RequirementsCatalogComponent)
  },
  {
    path: 'admin/risks-catalog',
    canActivate: [adminGuard],
    loadComponent: () => import('./features/admin/risks-catalog/risks-catalog.component').then(m => m.RisksCatalogComponent)
  },
  
  {
    path: '',
    canActivate: [rootGuard],
    pathMatch: 'full',
    children: []
  },
  {
    path: '**',
    redirectTo: '/login'
  }
];
