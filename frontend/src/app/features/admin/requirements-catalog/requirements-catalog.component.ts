import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-requirements-catalog',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="page-container">
      <h2>Справочник требований</h2>
      <p>Админ-панель: управление справочником требований НПА</p>
      <ul>
        <li>Код требования</li>
        <li>Название</li>
        <li>Ссылка на НПА</li>
        <li>Категория НВОС</li>
        <li>Водопользование</li>
        <li>Побочные продукты</li>
      </ul>
      <p><em>CRUD интерфейс будет реализован в следующей итерации</em></p>
    </div>
  `,
  styles: [`
    .page-container {
      padding: 1rem;
    }
  `]
})
export class RequirementsCatalogComponent {}
