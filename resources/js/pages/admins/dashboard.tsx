import { Head, Link } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';
import { LogOut } from 'lucide-react';

import AppLayout from '@/components/layouts/app-layout';
import PlaceholderPattern from '@/components/elements/placeholder-pattern';
import { Button } from '@/components/elements/button';

const breadcrumbs: BreadcrumbItem[] = [{
  title: 'Dashboard',
  href: '/dashboard',
}];

export default function Dashboard() {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Dashboard" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex items-center justify-between gap-4">
          <h1 className="text-2xl font-semibold">Dashboard Admin</h1>
          <Button variant="destructive" asChild>
            <Link href={route('logout')} method="post" as="button">
              <LogOut />
              Keluar
            </Link>
          </Button>
        </div>
        <div className="grid auto-rows-min gap-4 md:grid-cols-3">
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">
            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
          </div>
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">
            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
          </div>
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">
            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
          </div>
        </div>
        <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border md:min-h-min">
          <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
        </div>
      </div>
    </AppLayout>
  );
};