import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface ContractDto {
  id: number;
  org_id: number;
  number: string;
  signed_at: string;
  status: string;
}

export interface InvoiceDto {
  id: number;
  contract_id: number;
  number: string;
  amount: number;
  issued_at: string;
  paid_at: string | null;
}

export interface ActDto {
  id: number;
  contract_id: number;
  number: string;
  accepted_at: string | null;
}

@Injectable({ providedIn: 'root' })
export class FinanceService {
  private http = inject(HttpClient);

  listContracts(orgId: number): Observable<ContractDto[]> {
    return this.http.get<ContractDto[]>(`/api/contract?orgId=${orgId}`);
  }

  listInvoices(contractId: number): Observable<InvoiceDto[]> {
    return this.http.get<InvoiceDto[]>(`/api/invoice?contractId=${contractId}`);
  }

  listActs(contractId: number): Observable<ActDto[]> {
    return this.http.get<ActDto[]>(`/api/act?contractId=${contractId}`);
  }
}
