import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { toSignal } from '@angular/core/rxjs-interop';
import { RequirementService, RequirementDto } from '../../services/requirement.service';
import { OrganizationService } from '../../services/organization.service';

@Component({
  selector: 'app-requirements',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <h3>Требования</h3>

    <label>Организация:
      <select [(ngModel)]="selectedOrgId" (change)="load()">
        <option *ngFor="let o of orgs()" [value]="o.id">{{ o.name }}</option>
      </select>
    </label>

    <ul>
      <li *ngFor="let r of requirements()">
        {{ r.requirement.title }} - статус {{ r.status }}
      </li>
    </ul>
  `
})
export class RequirementsComponent {
  private orgSvc = inject(OrganizationService);
  private reqSvc = inject(RequirementService);

  orgs = toSignal(this.orgSvc.list(), { initialValue: [] });
  requirements = signal<RequirementDto[]>([]);

  selectedOrgId: number | null = null;

  load() {
    if (!this.selectedOrgId) return;
    this.reqSvc.list(this.selectedOrgId).subscribe(res => this.requirements.set(res));
  }
}
