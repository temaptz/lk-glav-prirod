import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { toSignal } from '@angular/core/rxjs-interop';
import { CalendarService, CalendarEventDto } from '../../services/calendar.service';
import { OrganizationService } from '../../services/organization.service';

@Component({
  selector: 'app-calendar',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <h3>Календарь</h3>
    <label>Организация:
      <select [(ngModel)]="orgId" (change)="load()">
        <option *ngFor="let o of orgs()" [value]="o.id">{{ o.name }}</option>
      </select>
    </label>

    <table *ngIf="events().length">
      <tr><th>Дата</th><th>Событие</th></tr>
      <tr *ngFor="let e of events()">
        <td>{{ e.event_date }}</td><td>{{ e.title }}</td>
      </tr>
    </table>
  `
})
export class CalendarComponent {
  private orgSvc = inject(OrganizationService);
  private calSvc = inject(CalendarService);

  orgs = toSignal(this.orgSvc.list(), { initialValue: [] });
  events = signal<CalendarEventDto[]>([]);
  orgId: number | null = null;

  load() {
    if (!this.orgId) return;
    this.calSvc.list(this.orgId).subscribe(ev => this.events.set(ev));
  }
}
