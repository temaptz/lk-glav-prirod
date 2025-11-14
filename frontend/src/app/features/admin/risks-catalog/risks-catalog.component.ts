import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-risks-catalog',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="page-container">
      <h2>Справочник рисков</h2>
      <p>Админ-панель: управление справочником рисков КоАП</p>
      <ul>
        <li>Статья КоАП</li>
        <li>Описание нарушения</li>
        <li>Минимальный штраф</li>
        <li>Максимальный штраф</li>
        <li>Привязка к требованиям</li>
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
export class RisksCatalogComponent {}
