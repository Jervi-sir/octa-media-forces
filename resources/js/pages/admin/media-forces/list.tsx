import React from 'react';
import { AdminLayout } from '../layout/admin-layout';
import { Head, Link } from "@inertiajs/react";
import { Card, CardContent } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import MediaForceController from '@/actions/App/Http/Controllers/Admin/MediaForceController';

type ForceRow = {
  id: number;
  name: string | null;
  email: string;
  videos_total: number;
  videos_submitted_count: number;
  videos_approved_count: number;
};

type Paginated<T> = {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
};

export default function Page({ forces }: { forces: Paginated<ForceRow> }) {
  return (
    <AdminLayout>
      <Head title="Media Forces" />
      <div className="px-6 py-6">
        <h1 className="text-2xl font-semibold mb-4">Media Forces</h1>

        <Card className="shadow-sm">
          <CardContent className="p-0">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Email</TableHead>
                  {/* <TableHead>Total Videos</TableHead> */}
                  <TableHead>Submitted</TableHead>
                  <TableHead>Approved</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {forces.data.map((f) => (
                  <TableRow key={f.id}>
                    <TableCell>{f.id}</TableCell>
                    <TableCell>{f.name ?? "—"}</TableCell>
                    <TableCell>{f.email}</TableCell>
                    {/* <TableCell>{f.videos_total}</TableCell> */}
                    <TableCell>{f.videos_submitted_count}</TableCell>
                    <TableCell>{f.videos_approved_count}</TableCell>
                    <TableCell className="text-right">
                      <Button asChild size="sm" variant="secondary">
                        <Link href={MediaForceController.show({ mediaForce: f.id })}>Open</Link>
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        {/* Simple pagination */}
        <div className="flex gap-2 mt-4 flex-wrap">
          {forces.links.map((l, i) => (
            <Button
              key={i}
              asChild
              size="sm"
              variant={l.active ? "default" : "outline"}
              disabled={!l.url}
            >
              <Link href={l.url ?? "#"} dangerouslySetInnerHTML={{ __html: l.label }} />
            </Button>
          ))}
        </div>
      </div>
    </AdminLayout>
  );
};
