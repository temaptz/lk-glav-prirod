import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface ArtifactDto {
  id: number;
  path: string;
  filename?: string;
  mime: string;
  uploaded_at: string;
  url: string;
}

@Injectable({ providedIn: 'root' })
export class ArtifactService {
  private http = inject(HttpClient);

  list(orgId: number): Observable<ArtifactDto[]> {
    return this.http.get<ArtifactDto[]>(`/api/artifact?orgId=${orgId}`);
  }

  upload(orgId: number, file: string, filename: string): Observable<ArtifactDto> {
    return this.http.post<ArtifactDto>(`/api/artifact/upload?orgId=${orgId}`, { file, filename });
  }
}
