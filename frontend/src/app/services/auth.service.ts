import { Injectable, signal, inject, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/operators';

export interface LoginDto { email: string; password: string; }
export interface AuthResponse { access_token: string; }

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);

  readonly token = signal<string | null>(this.getValidToken());
  readonly userEmail = signal<string | null>(this.getUserEmail());
  readonly userRole = signal<string | null>(this.getUserRole());

  readonly isLoggedIn = computed(() => !!this.token());
  readonly isAdmin = computed(() => this.userRole() === 'admin');
  readonly isManager = computed(() => this.userRole() === 'manager');
  readonly isSpecialist = computed(() => this.userRole() === 'specialist');
  readonly isClient = computed(() => this.userRole() === 'client');

  private getValidToken(): string | null {
    const token = localStorage.getItem('jwt');
    if (!token) return null;

    try {
      // Decode JWT payload (base64)
      const payload = JSON.parse(atob(token.split('.')[1]));
      const exp = payload.exp * 1000; // Convert to milliseconds
      
      // Check if token is expired
      if (Date.now() >= exp) {
        localStorage.removeItem('jwt');
        return null;
      }
      
      return token;
    } catch (e) {
      // Invalid token format
      localStorage.removeItem('jwt');
      return null;
    }
  }

  private getUserEmail(): string | null {
    const token = this.getValidToken();
    if (!token) return null;

    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      return payload.email || null;
    } catch (e) {
      return null;
    }
  }

  private getUserRole(): string | null {
    const token = this.getValidToken();
    if (!token) return null;

    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      return payload.role || null;
    } catch (e) {
      return null;
    }
  }

  login(dto: LoginDto) {
    return this.http.post<AuthResponse>('/api/auth/login', dto).pipe(
      tap(res => {
        localStorage.setItem('jwt', res.access_token);
        this.token.set(res.access_token);
        this.userEmail.set(this.getUserEmail());
        this.userRole.set(this.getUserRole());
      })
    );
  }

  logout() {
    localStorage.removeItem('jwt');
    this.token.set(null);
    this.userEmail.set(null);
    this.userRole.set(null);
  }
}
