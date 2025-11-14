import { Component, inject, signal, OnInit, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { OrganizationService, OrganizationDto } from '../../services/organization.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-clients',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="page-container">
      <div class="page-header">
        <div>
          <h2>Клиенты</h2>
          <p class="subtitle">Управление организациями-клиентами</p>
        </div>
        <div class="header-stats">
          <div class="stat-card">
            <div class="stat-value">{{ filteredOrganizations().length }}</div>
            <div class="stat-label">Всего клиентов</div>
          </div>
        </div>
      </div>

      <div *ngIf="error()" class="alert alert-error">{{ error() }}</div>

      <!-- Filters -->
      <div class="filters-panel">
        <div class="filter-group">
          <label>Поиск</label>
          <input 
            type="text" 
            [(ngModel)]="searchQuery" 
            (input)="applyFilters()"
            placeholder="Название, ИНН, ОГРН..."
            class="search-input"
          >
        </div>
        <div class="filter-group">
          <label>Категория НВОС</label>
          <select [(ngModel)]="categoryFilter" (change)="applyFilters()">
            <option value="">Все</option>
            <option value="1">I категория</option>
            <option value="2">II категория</option>
            <option value="3">III категория</option>
            <option value="4">IV категория</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Водопользование</label>
          <select [(ngModel)]="waterFilter" (change)="applyFilters()">
            <option value="">Все</option>
            <option value="река">Река</option>
            <option value="скважина">Скважина</option>
            <option value="none">Нет</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Побочные продукты</label>
          <select [(ngModel)]="byproductFilter" (change)="applyFilters()">
            <option value="">Все</option>
            <option value="true">Да</option>
            <option value="false">Нет</option>
          </select>
        </div>
        <button (click)="resetFilters()" class="btn-secondary">Сбросить</button>
      </div>

      <!-- Organizations Table -->
      <div class="organizations-table" *ngIf="!loading(); else loadingTpl">
        <table>
          <thead>
            <tr>
              <th>Название</th>
              <th>ИНН</th>
              <th>ОГРН</th>
              <th>Категория НВОС</th>
              <th>Водопользование</th>
              <th>Побочные продукты</th>
              <th>Дата создания</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <tr *ngFor="let org of filteredOrganizations()">
              <td>
                <div class="org-name">{{ org.name }}</div>
              </td>
              <td>{{ org.inn }}</td>
              <td>{{ org.ogrn }}</td>
              <td>
                <span class="category-badge" [class]="'category-' + org.category">
                  {{ getCategoryLabel(org.category) }}
                </span>
              </td>
              <td>
                <span class="badge" *ngIf="org.water_source">{{ org.water_source }}</span>
                <span class="badge badge-gray" *ngIf="!org.water_source">Нет</span>
              </td>
              <td>
                <span class="badge" [class.badge-success]="org.has_byproduct" [class.badge-gray]="!org.has_byproduct">
                  {{ org.has_byproduct ? 'Да' : 'Нет' }}
                </span>
              </td>
              <td>{{ formatDate(org.created_at) }}</td>
              <td class="actions">
                <button class="btn-small btn-view" (click)="viewDetails(org)">
                  Детали
                </button>
              </td>
            </tr>
            <tr *ngIf="filteredOrganizations().length === 0">
              <td colspan="8" class="empty-state">
                {{ organizations().length === 0 ? 'Нет доступных клиентов' : 'Ничего не найдено' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <ng-template #loadingTpl>
        <div class="loading">Загрузка клиентов...</div>
      </ng-template>

      <!-- Details Modal -->
      <div class="modal-overlay" *ngIf="selectedOrg()" (click)="closeDetails()">
        <div class="modal-content modal-large" (click)="$event.stopPropagation()">
          <div class="modal-header">
            <h3>{{ selectedOrg()?.name }}</h3>
            <button class="close-btn" (click)="closeDetails()">&times;</button>
          </div>
          <div class="modal-body">
            <div class="details-grid">
              <div class="detail-section">
                <h4>Основная информация</h4>
                <div class="detail-row">
                  <span class="detail-label">Название:</span>
                  <span class="detail-value">{{ selectedOrg()?.name }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">ИНН:</span>
                  <span class="detail-value">{{ selectedOrg()?.inn }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">ОГРН:</span>
                  <span class="detail-value">{{ selectedOrg()?.ogrn }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Дата создания:</span>
                  <span class="detail-value">{{ formatDate(selectedOrg()?.created_at || '') }}</span>
                </div>
              </div>

              <div class="detail-section">
                <h4>Профиль экологии</h4>
                <div class="detail-row">
                  <span class="detail-label">Категория НВОС:</span>
                  <span class="category-badge" [class]="'category-' + selectedOrg()?.category">
                    {{ getCategoryLabel(selectedOrg()?.category || 0) }}
                  </span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Водопользование:</span>
                  <span class="detail-value">{{ selectedOrg()?.water_source || 'Нет' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Побочные продукты:</span>
                  <span class="detail-value">{{ selectedOrg()?.has_byproduct ? 'Да' : 'Нет' }}</span>
                </div>
              </div>
            </div>

            <div class="detail-section" *ngIf="selectedOrgId()">
              <h4>Статистика</h4>
              <p class="info-message">
                <em>Для просмотра детальной статистики перейдите в соответствующие разделы:</em>
              </p>
              <div class="quick-links">
                <a [routerLink]="['/requirements']" [queryParams]="{org_id: selectedOrgId()}" class="link-button">
                  📋 Требования
                </a>
                <a [routerLink]="['/artifacts']" [queryParams]="{org_id: selectedOrgId()}" class="link-button">
                  📁 Артефакты
                </a>
                <a [routerLink]="['/calendar']" [queryParams]="{org_id: selectedOrgId()}" class="link-button">
                  📅 Календарь
                </a>
                <a [routerLink]="['/finance']" [queryParams]="{org_id: selectedOrgId()}" class="link-button">
                  💰 Финансы
                </a>
              </div>
            </div>
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
      align-items: flex-start;
      margin-bottom: 2rem;
    }

    .page-header h2 {
      margin: 0 0 0.5rem 0;
    }

    .subtitle {
      color: #666;
      font-size: 14px;
      margin: 0;
    }

    .header-stats {
      display: flex;
      gap: 1rem;
    }

    .stat-card {
      background: white;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      text-align: center;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: var(--primary);
    }

    .stat-label {
      font-size: 12px;
      color: #666;
      margin-top: 0.25rem;
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

    .filters-panel {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      margin-bottom: 1.5rem;
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      align-items: flex-end;
    }

    .filter-group {
      flex: 1;
      min-width: 200px;
    }

    .filter-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      font-size: 13px;
      color: #333;
    }

    .filter-group input,
    .filter-group select {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }

    .filter-group input:focus,
    .filter-group select:focus {
      outline: none;
      border-color: var(--primary);
    }

    .search-input {
      padding-left: 2.5rem;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23666' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: 0.75rem center;
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

    .organizations-table {
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

    .org-name {
      font-weight: 500;
      color: #333;
    }

    .category-badge {
      display: inline-block;
      padding: 0.25rem 0.6rem;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .category-1 {
      background-color: #dc3545;
      color: white;
    }

    .category-2 {
      background-color: #ffc107;
      color: #000;
    }

    .category-3 {
      background-color: #17a2b8;
      color: white;
    }

    .category-4 {
      background-color: #28a745;
      color: white;
    }

    .badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;
      background-color: #e7f3ff;
      color: #0066cc;
    }

    .badge-success {
      background-color: #d4edda;
      color: #155724;
    }

    .badge-gray {
      background-color: #e9ecef;
      color: #6c757d;
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

    .btn-view {
      background-color: #007bff;
      color: white;
    }

    .btn-view:hover {
      background-color: #0056b3;
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
      max-width: 800px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .modal-large {
      max-width: 900px;
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
      font-size: 20px;
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

    .details-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      margin-bottom: 2rem;
    }

    .detail-section {
      background: #f8f9fa;
      padding: 1.5rem;
      border-radius: 8px;
    }

    .detail-section h4 {
      margin: 0 0 1rem 0;
      font-size: 16px;
      color: var(--primary-dark);
      border-bottom: 2px solid var(--primary);
      padding-bottom: 0.5rem;
    }

    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid #e0e0e0;
    }

    .detail-row:last-child {
      border-bottom: none;
    }

    .detail-label {
      font-weight: 500;
      color: #666;
      font-size: 14px;
    }

    .detail-value {
      font-weight: 500;
      color: #333;
      font-size: 14px;
    }

    .info-message {
      color: #666;
      font-size: 13px;
      margin-bottom: 1rem;
    }

    .quick-links {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }

    .link-button {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      background: white;
      border: 2px solid var(--primary);
      border-radius: 8px;
      color: var(--primary);
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s;
    }

    .link-button:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
  `]
})
export class ClientsComponent implements OnInit {
  private readonly orgService = inject(OrganizationService);
  private readonly authService = inject(AuthService);

  organizations = signal<OrganizationDto[]>([]);
  loading = signal(false);
  error = signal<string | null>(null);
  
  selectedOrg = signal<OrganizationDto | null>(null);
  selectedOrgId = computed(() => this.selectedOrg()?.id || null);

  // Filters
  searchQuery = '';
  categoryFilter = '';
  waterFilter = '';
  byproductFilter = '';

  filteredOrganizations = signal<OrganizationDto[]>([]);

  readonly isAdmin = this.authService.isAdmin;
  readonly userRole = this.authService.userRole;

  ngOnInit() {
    this.loadOrganizations();
  }

  loadOrganizations() {
    this.loading.set(true);
    this.error.set(null);
    
    this.orgService.list().subscribe({
      next: (orgs) => {
        this.organizations.set(orgs);
        this.filteredOrganizations.set(orgs);
        this.loading.set(false);
      },
      error: (err) => {
        this.error.set('Ошибка загрузки клиентов: ' + (err.error?.message || err.message));
        this.loading.set(false);
      }
    });
  }

  applyFilters() {
    let filtered = this.organizations();

    // Search filter
    if (this.searchQuery.trim()) {
      const query = this.searchQuery.toLowerCase();
      filtered = filtered.filter(org => 
        org.name.toLowerCase().includes(query) ||
        org.inn.includes(query) ||
        org.ogrn.includes(query)
      );
    }

    // Category filter
    if (this.categoryFilter) {
      filtered = filtered.filter(org => org.category === parseInt(this.categoryFilter));
    }

    // Water filter
    if (this.waterFilter) {
      if (this.waterFilter === 'none') {
        filtered = filtered.filter(org => !org.water_source);
      } else {
        filtered = filtered.filter(org => org.water_source === this.waterFilter);
      }
    }

    // Byproduct filter
    if (this.byproductFilter) {
      const hasProduct = this.byproductFilter === 'true';
      filtered = filtered.filter(org => org.has_byproduct === hasProduct);
    }

    this.filteredOrganizations.set(filtered);
  }

  resetFilters() {
    this.searchQuery = '';
    this.categoryFilter = '';
    this.waterFilter = '';
    this.byproductFilter = '';
    this.filteredOrganizations.set(this.organizations());
  }

  getCategoryLabel(category: number): string {
    const labels: Record<number, string> = {
      1: 'I категория',
      2: 'II категория',
      3: 'III категория',
      4: 'IV категория'
    };
    return labels[category] || `Категория ${category}`;
  }

  formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    });
  }

  viewDetails(org: OrganizationDto) {
    this.selectedOrg.set(org);
  }

  closeDetails() {
    this.selectedOrg.set(null);
  }
}
