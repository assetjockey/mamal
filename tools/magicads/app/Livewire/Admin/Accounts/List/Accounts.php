<?php

namespace App\Livewire\Admin\Accounts\List;

use Livewire\Component;
use App\Models\User;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Masmerise\Toaster\Toaster;

class Accounts extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    protected static $currencies;

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->queryStringIdentifier('users')
            ->query(fn (): Builder => User::query())
            ->recordActionsColumnLabel(__('Actions'))
            ->recordClasses('border-b border-(--default-border-color) shadow-none hover:bg-(--default-element-light-bg-color) transition-colors')
            ->columns([
                TextColumn::make('name')
                        ->label(__('User'))
                        ->formatStateUsing(function ($state, $record) {
                            $avatar = $record->avatar ?? '';
                            $name = $record->name ?? '';
                            $email = $record->email ?? '';
                            return '<div class="flex items-center gap-3"><img src="' . asset($avatar) . '" class="w-10 h-10 rounded-full" /><div><div>' . $name . '</div><div class="text-sm text-gray-500">' . $email . '</div></div></div>';
                        })
                        ->html()
                        ->toggleable()
                        ->searchable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('group')
                        ->label(__('Group'))
                        ->toggleable()
                        ->searchable()
                        ->sortable()
                        ->html()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'user' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-neutral-100 text-gray-800">User</span>',
                            'admin' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-red-100 text-red-800">Admin</span>',
                            'subscriber' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-green-100 text-green-800">Subscriber</span>',
                        })
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
                            'pending' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-neutral-100 text-gray-800">Pending</span>',
                            'suspended' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-red-100 text-red-800">Suspended</span>',
                            'inactive' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-red-100 text-red-800">Inactive</span>',
                            'active' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-green-100 text-green-800">Active</span>',
                        })
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),              
                TextColumn::make('credits')
                        ->label(__('Credits'))
                        ->toggleable()
                        ->sortable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),  
                TextColumn::make('created_at')
                        ->label(__('Created'))
                        ->dateTime('M j, Y')
                        ->toggleable()
                        ->sortable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),                        
                
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'deactive' => 'Deactive',
                        'pending' => 'Pending',
                ]),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                 ActionGroup::make([
                    Action::make('view')
                        ->url(fn (User $record): string => route('admin.accounts.view', $record->user_id))
                        ->icon('heroicon-o-folder-open')
                        ->extraAttributes(['class' => 'custom-action-button-view']),                     
                    DeleteAction::make()
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->successNotification(null)
                        ->using(function ($record) {
                            $result = $record->delete();
                            if ($result) {
                                app(Toaster::class)->success(__('User deleted successfully'));
                            } else {
                                app(Toaster::class)->error(__('Failed to delete the user'));
                            }
                            return $result;
                        }),
                ])
            ])
            ->toolbarActions([
                Action::make('create_user')
                        ->label(__('Create New User'))
                        ->url(route('admin.accounts.create'))
                        ->icon('heroicon-o-plus')
                        ->extraAttributes(['class' => 'table-action-button']),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateHeading(__('No users yet')) 
            ->emptyStateDescription(__('Once you get user registered, they will appear here.'))
            ->extraAttributes([
                'class' => 'shadow-none rounded-lg overflow-hidden border border-(--default-border-color)',
            ]);

    }


    public function render()
    {
        return view('livewire.admin.accounts.users.index', ['total' => User::count()])->title(__('Users') . ' | ' . config('app.name'));
    }
}
