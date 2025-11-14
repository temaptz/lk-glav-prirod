import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface OrganizationDto {
  id: number;
  name: string;
  inn: string;
  ogrn: string;
  category: number;
  water_source?: string | null;
  has_byproduct: boolean;
  created_at: string;
}

export interface OrganizationStats {
  total_requirements: number;
  completed_requirements: number;
  pending_requirements: number;
  total_contracts: number;
  total_invoices: number;
  total_acts: number;
}

@Injectable({ providedIn: 'root' })
export class OrganizationService {
  private readonly http = inject(HttpClient);

  /** GET /api/organization */
  list(): Observable<OrganizationDto[]> {
    return this.http.get<OrganizationDto[]>('/api/organization');
  }

  /** GET /api/organization/:id */
  getById(id: number): Observable<OrganizationDto> {
    return this.http.get<OrganizationDto>(`/api/organization/${id}`);
  }

  /** GET /api/organization/:id/stats */
  getStats(id: number): Observable<OrganizationStats> {
    return this.http.get<OrganizationStats>(`/api/organization/${id}/stats`);
  }
}
