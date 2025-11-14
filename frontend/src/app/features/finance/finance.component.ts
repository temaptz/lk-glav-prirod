import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { toSignal } from '@angular/core/rxjs-interop';
import { FinanceService, ContractDto, InvoiceDto, ActDto } from '../../services/finance.service';
import { OrganizationService } from '../../services/organization.service';

@Component({
  selector: 'app-finance',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <h3>Финансы (Договора / Счета / Акты)</h3>

    <label>Организация:
      <select [(ngModel)]="orgId" (change)="loadContracts()" name="org">
        <option *ngFor="let o of orgs()" [value]="o.id">{{ o.name }}</option>
      </select>
    </label>

    <h4>Договора</h4>
    <table *ngIf="contracts().length">
      <thead>
        <tr><th>Номер</th><th>Дата</th><th>Статус</th><th>Действия</th></tr>
      </thead>
      <tbody>
        <tr *ngFor="let c of contracts()">
          <td>{{ c.number }}</td>
          <td>{{ c.signed_at }}</td>
          <td>{{ c.status }}</td>
          <td>
            <button (click)="selectContract(c.id)">Счета/Акты</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div *ngIf="selectedContractId()">
      <h4>Счета по договору #{{ selectedContractId() }}</h4>
      <ul>
        <li *ngFor="let inv of invoices()">
          Счет {{ inv.number }} - {{ inv.amount }} ₽ 
          (выставлен {{ inv.issued_at }}, оплачен {{ inv.paid_at || 'нет' }})
        </li>
      </ul>

      <h4>Акты по договору #{{ selectedContractId() }}</h4>
      <ul>
        <li *ngFor="let act of acts()">
          Акт {{ act.number }} (принят {{ act.accepted_at || 'нет' }})
        </li>
      </ul>
    </div>
  `
})
export class FinanceComponent {
  private orgSvc = inject(OrganizationService);
  private finSvc = inject(FinanceService);

  orgs = toSignal(this.orgSvc.list(), { initialValue: [] });
  contracts = signal<ContractDto[]>([]);
  invoices = signal<InvoiceDto[]>([]);
  acts = signal<ActDto[]>([]);
  orgId: number | null = null;
  selectedContractId = signal<number | null>(null);

  loadContracts() {
    if (!this.orgId) return;
    this.finSvc.listContracts(this.orgId).subscribe(res => {
      this.contracts.set(res);
      this.selectedContractId.set(null);
    });
  }

  selectContract(contractId: number) {
    this.selectedContractId.set(contractId);
    this.finSvc.listInvoices(contractId).subscribe(res => this.invoices.set(res));
    this.finSvc.listActs(contractId).subscribe(res => this.acts.set(res));
  }
}
