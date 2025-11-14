import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService, LoginDto } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <div class="login-container">
      <h2>Личный кабинет</h2>
      <p class="subtitle">Система управления экологической отчётностью</p>
      <form (ngSubmit)="submit()" class="login-form">
        <div class="form-group">
          <label>Email</label>
          <input type="email" [(ngModel)]="model.email" name="email" required placeholder="admin&#64;example.com">
        </div>
        <div class="form-group">
          <label>Пароль</label>
          <input type="password" [(ngModel)]="model.password" name="password" required>
        </div>
        <button type="submit" class="btn-login">Войти</button>
      </form>
      <p *ngIf="error()" class="error-message">{{ error() }}</p>
      <div class="demo-credentials">
        <p><small><strong>Демо-учётные записи:</strong></small></p>
        <p><small>admin&#64;example.com / admin</small></p>
        <p><small>client&#64;example.com / client</small></p>
      </div>
    </div>
  `,
  styles: [`
    .login-container {
      background: white;
      padding: 2.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
    }
    
    .login-container h2 {
      text-align: center;
      color: var(--primary-dark);
      margin-bottom: 0.5rem;
    }
    
    .subtitle {
      text-align: center;
      color: #666;
      font-size: 13px;
      margin-bottom: 2rem;
    }
    
    .login-form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    
    .form-group {
      display: flex;
      flex-direction: column;
    }
    
    .form-group label {
      margin-bottom: 0.4rem;
      font-weight: 500;
      font-size: 13px;
    }
    
    .form-group input {
      padding: 0.6rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
    }
    
    .btn-login {
      margin-top: 0.5rem;
      padding: 0.75rem;
      font-size: 15px;
      font-weight: 500;
    }
    
    .error-message {
      color: #dc3545;
      text-align: center;
      margin-top: 1rem;
      font-size: 13px;
    }
    
    .demo-credentials {
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid #e0e0e0;
      text-align: center;
      color: #666;
    }
    
    .demo-credentials p {
      margin: 0.25rem 0;
    }
  `]
})
export class LoginComponent {
  private auth = inject(AuthService);
  private router = inject(Router);

  model: LoginDto = { email: '', password: '' };
  error = signal<string | null>(null);

  submit() {
    this.error.set(null);
    this.auth.login(this.model).subscribe({
      next: () => this.router.navigateByUrl('/home'),
      error: err => this.error.set(err.error?.message || 'Ошибка входа')
    });
  }
}
