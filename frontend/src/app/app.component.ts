import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink, RouterLinkActive, Router } from '@angular/router';
import { AuthService } from './services/auth.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './app.component.html',
  styles: []
})
export class AppComponent {
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  readonly title = 'Личный кабинет клиента по экологии';
  readonly isLoggedIn = this.authService.isLoggedIn;
  readonly userEmail = this.authService.userEmail;
  readonly userRole = this.authService.userRole;
  readonly isAdmin = this.authService.isAdmin;
  readonly isManager = this.authService.isManager;
  readonly isSpecialist = this.authService.isSpecialist;
  readonly isClient = this.authService.isClient;

  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }
}
