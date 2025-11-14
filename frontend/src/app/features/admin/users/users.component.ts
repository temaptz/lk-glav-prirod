import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminUserService, User, CreateUserDto, UpdateUserDto } from '../../../services/admin-user.service';

@Component({
  selector: 'app-admin-users',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <div class="page-container">
      <div class="page-header">
        <h2>Управление пользователями</h2>
        <button (click)="openCreateModal()" class="btn-primary">
          + Создать пользователя
        </button>
      </div>

      <div *ngIf="error()" class="alert alert-error">{{ error() }}</div>
      <div *ngIf="success()" class="alert alert-success">{{ success() }}</div>

      <div class="users-table" *ngIf="!loading(); else loadingTpl">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Роль</th>
              <th>Статус</th>
              <th>Дата создания</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <tr *ngFor="let user of users()">
              <td>{{ user.id }}</td>
              <td>{{ user.email }}</td>
              <td>
                <span class="role-badge" [class]="'role-' + user.role">
                  {{ getRoleLabel(user.role) }}
                </span>
              </td>
              <td>
                <span class="status-badge" [class.active]="user.is_active" [class.inactive]="!user.is_active">
                  {{ user.is_active ? 'Активен' : 'Неактивен' }}
                </span>
              </td>
              <td>{{ formatDate(user.created_at) }}</td>
              <td class="actions">
                <button (click)="openEditModal(user)" class="btn-small btn-edit">
                  Изменить
                </button>
                <button (click)="confirmDelete(user)" class="btn-small btn-delete">
                  Удалить
                </button>
              </td>
            </tr>
            <tr *ngIf="users().length === 0">
              <td colspan="6" class="empty-state">Пользователи не найдены</td>
            </tr>
          </tbody>
        </table>
      </div>

      <ng-template #loadingTpl>
        <div class="loading">Загрузка...</div>
      </ng-template>

      <!-- Create/Edit Modal -->
      <div class="modal-overlay" *ngIf="showModal()" (click)="closeModal()">
        <div class="modal-content" (click)="$event.stopPropagation()">
          <div class="modal-header">
            <h3>{{ editingUser() ? 'Редактирование пользователя' : 'Создание пользователя' }}</h3>
            <button class="close-btn" (click)="closeModal()">&times;</button>
          </div>
          
          <form (ngSubmit)="submitForm()" class="user-form">
            <div class="form-group">
              <label for="email">Email *</label>
              <input 
                type="email" 
                id="email" 
                [(ngModel)]="formData.email" 
                name="email" 
                required
                placeholder="user&#64;example.com"
              >
            </div>

            <div class="form-group" *ngIf="!editingUser()">
              <label for="password">Пароль *</label>
              <input 
                type="password" 
                id="password" 
                [(ngModel)]="formData.password" 
                name="password" 
                required
                placeholder="Минимум 6 символов"
              >
            </div>

            <div class="form-group" *ngIf="editingUser()">
              <label for="password">Новый пароль (оставьте пустым, если не меняете)</label>
              <input 
                type="password" 
                id="password" 
                [(ngModel)]="formData.password" 
                name="password"
                placeholder="Оставьте пустым для сохранения текущего"
              >
            </div>

            <div class="form-group">
              <label for="role">Роль *</label>
              <select id="role" [(ngModel)]="formData.role" name="role" required>
                <option value="client">Клиент</option>
                <option value="manager">Менеджер</option>
                <option value="specialist">Специалист</option>
                <option value="admin">Администратор</option>
              </select>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input 
                  type="checkbox" 
                  [(ngModel)]="formData.is_active" 
                  name="is_active"
                >
                Активен
              </label>
            </div>

            <div class="modal-actions">
              <button type="button" (click)="closeModal()" class="btn-secondary">
                Отмена
              </button>
              <button type="submit" class="btn-primary">
                {{ editingUser() ? 'Сохранить' : 'Создать' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div class="modal-overlay" *ngIf="showDeleteConfirm()" (click)="cancelDelete()">
        <div class="modal-content modal-small" (click)="$event.stopPropagation()">
          <div class="modal-header">
            <h3>Подтверждение удаления</h3>
          </div>
          <div class="modal-body">
            <p>Вы действительно хотите удалить пользователя <strong>{{ deletingUser()?.email }}</strong>?</p>
            <p class="warning-text">Это действие нельзя отменить.</p>
          </div>
          <div class="modal-actions">
            <button (click)="cancelDelete()" class="btn-secondary">
              Отмена
            </button>
            <button (click)="deleteUser()" class="btn-delete">
              Удалить
            </button>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .page-container {
      padding: 1.5rem;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .page-header h2 {
      margin: 0;
    }

    .alert {
      padding: 0.75rem 1rem;
      border-radius: 4px;
      margin-bottom: 1rem;
    }

    .alert-error {
      background-color: #fee;
      color: #c00;
      border: 1px solid #fcc;
    }

    .alert-success {
      background-color: #efe;
      color: #0a0;
      border: 1px solid #cfc;
    }

    .users-table {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      background-color: #f8f9fa;
      padding: 0.75rem 1rem;
      text-align: left;
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
      color: #666;
      border-bottom: 2px solid #e0e0e0;
    }

    td {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid #f0f0f0;
      font-size: 14px;
    }

    tr:hover {
      background-color: #fafafa;
    }

    .role-badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .role-admin {
      background-color: #dc3545;
      color: white;
    }

    .role-manager {
      background-color: #007bff;
      color: white;
    }

    .role-specialist {
      background-color: #17a2b8;
      color: white;
    }

    .role-client {
      background-color: #6c757d;
      color: white;
    }

    .status-badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;
    }

    .status-badge.active {
      background-color: #d4edda;
      color: #155724;
    }

    .status-badge.inactive {
      background-color: #f8d7da;
      color: #721c24;
    }

    .actions {
      display: flex;
      gap: 0.5rem;
    }

    .btn-small {
      padding: 0.35rem 0.75rem;
      font-size: 13px;
      border-radius: 4px;
      border: none;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-edit {
      background-color: #ffc107;
      color: #000;
    }

    .btn-edit:hover {
      background-color: #e0a800;
    }

    .btn-delete {
      background-color: #dc3545;
      color: white;
    }

    .btn-delete:hover {
      background-color: #c82333;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
      padding: 0.6rem 1.25rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: background 0.2s;
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
      padding: 0.6rem 1.25rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.2s;
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    .loading {
      text-align: center;
      padding: 3rem;
      color: #999;
    }

    .empty-state {
      text-align: center;
      padding: 3rem;
      color: #999;
    }

    /* Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    .modal-content {
      background: white;
      border-radius: 8px;
      width: 90%;
      max-width: 500px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .modal-small {
      max-width: 400px;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #e0e0e0;
    }

    .modal-header h3 {
      margin: 0;
      font-size: 18px;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 28px;
      line-height: 1;
      cursor: pointer;
      color: #999;
      padding: 0;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .close-btn:hover {
      color: #333;
    }

    .modal-body {
      padding: 1.5rem;
    }

    .warning-text {
      color: #dc3545;
      font-size: 13px;
      margin-top: 0.5rem;
    }

    .user-form {
      padding: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      font-size: 14px;
      color: #333;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--primary);
    }

    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
      width: auto;
      cursor: pointer;
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 1.5rem;
    }
  `]
})
export class UsersComponent implements OnInit {
  private readonly adminUserService = inject(AdminUserService);

  users = signal<User[]>([]);
  loading = signal(false);
  error = signal<string | null>(null);
  success = signal<string | null>(null);
  
  showModal = signal(false);
  showDeleteConfirm = signal(false);
  editingUser = signal<User | null>(null);
  deletingUser = signal<User | null>(null);

  formData: any = {
    email: '',
    password: '',
    role: 'client',
    is_active: true
  };

  ngOnInit() {
    this.loadUsers();
  }

  loadUsers() {
    this.loading.set(true);
    this.error.set(null);
    
    this.adminUserService.getUsers().subscribe({
      next: (users) => {
        this.users.set(users);
        this.loading.set(false);
      },
      error: (err) => {
        this.error.set('Ошибка загрузки пользователей: ' + (err.error?.message || err.message));
        this.loading.set(false);
      }
    });
  }

  openCreateModal() {
    this.editingUser.set(null);
    this.formData = {
      email: '',
      password: '',
      role: 'client',
      is_active: true
    };
    this.showModal.set(true);
    this.clearMessages();
  }

  openEditModal(user: User) {
    this.editingUser.set(user);
    this.formData = {
      email: user.email,
      password: '',
      role: user.role,
      is_active: user.is_active
    };
    this.showModal.set(true);
    this.clearMessages();
  }

  closeModal() {
    this.showModal.set(false);
    this.editingUser.set(null);
  }

  submitForm() {
    const user = this.editingUser();
    
    if (user) {
      // Update
      const dto: UpdateUserDto = {
        email: this.formData.email,
        role: this.formData.role,
        is_active: this.formData.is_active
      };
      
      if (this.formData.password) {
        dto.password = this.formData.password;
      }

      this.adminUserService.updateUser(user.id, dto).subscribe({
        next: () => {
          this.success.set('Пользователь успешно обновлён');
          this.closeModal();
          this.loadUsers();
          this.clearMessagesAfterDelay();
        },
        error: (err) => {
          this.error.set('Ошибка обновления: ' + (err.error?.message || err.message));
        }
      });
    } else {
      // Create
      const dto: CreateUserDto = {
        email: this.formData.email,
        password: this.formData.password,
        role: this.formData.role,
        is_active: this.formData.is_active
      };

      this.adminUserService.createUser(dto).subscribe({
        next: () => {
          this.success.set('Пользователь успешно создан');
          this.closeModal();
          this.loadUsers();
          this.clearMessagesAfterDelay();
        },
        error: (err) => {
          this.error.set('Ошибка создания: ' + (err.error?.message || err.message));
        }
      });
    }
  }

  confirmDelete(user: User) {
    this.deletingUser.set(user);
    this.showDeleteConfirm.set(true);
    this.clearMessages();
  }

  cancelDelete() {
    this.showDeleteConfirm.set(false);
    this.deletingUser.set(null);
  }

  deleteUser() {
    const user = this.deletingUser();
    if (!user) return;

    this.adminUserService.deleteUser(user.id).subscribe({
      next: () => {
        this.success.set(`Пользователь ${user.email} успешно удалён`);
        this.cancelDelete();
        this.loadUsers();
        this.clearMessagesAfterDelay();
      },
      error: (err) => {
        this.error.set('Ошибка удаления: ' + (err.error?.message || err.message));
        this.cancelDelete();
      }
    });
  }

  getRoleLabel(role: string): string {
    const labels: Record<string, string> = {
      admin: 'Админ',
      manager: 'Менеджер',
      specialist: 'Специалист',
      client: 'Клиент'
    };
    return labels[role] || role;
  }

  formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  clearMessages() {
    this.error.set(null);
    this.success.set(null);
  }

  clearMessagesAfterDelay() {
    setTimeout(() => this.clearMessages(), 3000);
  }
}
