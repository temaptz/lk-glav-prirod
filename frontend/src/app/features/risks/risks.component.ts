import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { toSignal } from '@angular/core/rxjs-interop';
import { RiskService } from '../../services/risk.service';

@Component({
  selector: 'app-risks',
  standalone: true,
  imports: [CommonModule],
  template: `
    <h3>Риски (КоАП)</h3>

    <table *ngIf="risks().length">
      <thead>
        <tr>
          <th>Статья КоАП</th>
          <th>Мин. штраф</th>
          <th>Макс. штраф</th>
          <th>Описание</th>
        </tr>
      </thead>
      <tbody>
        <tr *ngFor="let r of risks()">
          <td>{{ r.koap_article }}</td>
          <td>{{ r.min_fine }} ₽</td>
          <td>{{ r.max_fine }} ₽</td>
          <td>{{ r.description }}</td>
        </tr>
      </tbody>
    </table>

    <p *ngIf="!risks().length">Нет данных о рисках</p>
  `
})
export class RisksComponent {
  private riskSvc = inject(RiskService);

  risks = toSignal(this.riskSvc.list(), { initialValue: [] });
}
