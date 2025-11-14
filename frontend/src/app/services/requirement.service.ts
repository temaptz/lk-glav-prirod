import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface RequirementDto {
  id: number;
  requirement: { id: number; title: string };
  status: number;
  deadline: string | null;
}

@Injectable({ providedIn: 'root' })
export class RequirementService {
  private http = inject(HttpClient);

  list(orgId: number): Observable<RequirementDto[]> {
    return this.http.get<RequirementDto[]>(`/api/client-requirement?orgId=${orgId}`);
  }
}
