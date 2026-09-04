<?php

namespace App\Livewire\Admin\Support;

use App\Models\SupportTicket;
use Livewire\Component;
use Livewire\Attributes\Title;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Masmerise\Toaster\Toaster;

#[Title('Support Tickets')]
class Tickets extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->selectable()
            ->deferLoading()
            ->queryStringIdentifier('support_tickets')
            ->query(fn (): Builder => SupportTicket::query())
            ->recordActionsColumnLabel(__('Actions'))
            ->recordClasses('border-b border-(--default-border-color) shadow-none hover:bg-(--default-element-light-bg-color) transition-colors')
            ->columns([
                TextColumn::make('ticket_id')
                        ->label(__('Ticket ID'))
                        ->toggleable()
                        ->searchable()
                        ->sortable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('subject')
                        ->label(__('Subject'))
                        ->toggleable()
                        ->searchable()
                        ->sortable()
                        ->limit(30)
                        ->extraCellAttributes([
                            'class' => 'max-w-xs truncate',
                        ])
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('priority')
                        ->label(__('Priority'))
                        ->toggleable()
                        ->searchable()
                        ->sortable()
                        ->html()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'low' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-neutral-100 text-gray-800">Low</span>',
                            'high' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-yellow-100 text-yellow-800">High</span>',
                            'medium' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-green-100 text-green-800">Medium</span>',
                            'urgent' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-red-100 text-red-800">Urgent</span>',
                            default => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">' . ucfirst($state) . '</span>',
                        })
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('category')
                        ->label(__('Category'))
                        ->toggleable()
                        ->searchable()
                        ->sortable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('status')
                        ->label(__('Status'))
                        ->toggleable()
                        ->searchable()
                        ->sortable()
                        ->html()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'open' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-neutral-100 text-gray-800">Open</span>',
                            'in_progress' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-yellow-100 text-yellow-800">In Progress</span>',
                            'resolved' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-green-100 text-green-800">Resolved</span>',
                            'closed' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-red-100 text-red-800">Closed</span>',
                            default => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-xl bg-gray-100 text-gray-800">' . ucfirst($state) . '</span>',
                        })
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                    TextColumn::make('created_at')
                        ->label(__('Created On'))
                        ->dateTime('M j, Y')
                        ->description(fn ($record) => $record->created_at?->format('h:i A'))
                        ->toggleable()
                        ->sortable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('resolved_on')
                        ->label(__('Resolved On'))
                        ->dateTime('M j, Y')
                        ->description(fn ($record) => $record->resolved_on?->format('h:i A'))
                        ->toggleable()
                        ->sortable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                ]),
                SelectFilter::make('category')
                    ->options([
                        'technical' => 'Technical',
                        'billing' => 'Billing',
                        'account' => 'Account',
                        'general' => 'General',
                        'request' => 'Feature Request',
                ]),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                 ActionGroup::make([
                    Action::make('view')
                        ->url(fn (SupportTicket $record): string => route('admin.support.tickets.view', $record->ticket_id))
                        ->icon('heroicon-o-folder-open')
                        ->extraAttributes(['class' => 'custom-action-button-view']),                     
                    DeleteAction::make()
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->successNotification(null)
                        ->using(function ($record) {
                            $result = $record->delete();
                            if ($result) {
                                app(Toaster::class)->success(__('Ticket deleted successfully'));
                            } else {
                                app(Toaster::class)->error(__('Failed to delete ticket'));
                            }
                            return $result;
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateHeading(__('No support tickets yet'))
            ->emptyStateDescription(__('Once user created a ticket, it will appear here.'))
            ->extraAttributes([
                'class' => 'shadow-none rounded-lg overflow-hidden border border-(--default-border-color)',
            ]);

    }
    
    public function render()
    {
        return view('livewire.admin.support.tickets', [
            'openCount' => SupportTicket::where('status', 'open')->count(),
            'pendingCount' => SupportTicket::where('status', 'pending')->count(),
            'resolvedCount' => SupportTicket::where('status', 'resolved')->count(),
            'closedCount' => SupportTicket::where('status', 'closed')->count(),
        ]);
    }

}
