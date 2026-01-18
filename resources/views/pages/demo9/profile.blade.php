@extends('layouts.demo9.base')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
	<!-- Profile Header Card -->
	<div class="kt-card">
		<div class="kt-card-content flex flex-col lg:flex-row items-center gap-6 p-10">
			<div class="flex items-center gap-6">
				<img class="rounded-full size-24 shrink-0" src="{{ asset('assets/media/avatars/300-1.png') }}" alt="Profile Avatar">
				<div class="flex flex-col gap-2">
					<h2 class="text-2xl font-semibold text-mono">
						John Doe
					</h2>
					<div class="flex items-center gap-3 text-sm text-secondary-foreground">
						<span class="flex items-center gap-1.5">
							<i class="ki-filled ki-sms text-base"></i>
							john.doe@example.com
						</span>
						<span class="flex items-center gap-1.5">
							<i class="ki-filled ki-phone text-base"></i>
							+1 234 567 8900
						</span>
					</div>
					<div class="flex items-center gap-2 mt-2">
						<span class="kt-badge kt-badge-success kt-badge-sm">Active</span>
						<span class="kt-badge kt-badge-outline kt-badge-sm">Premium User</span>
					</div>
				</div>
			</div>
			<div class="flex gap-2 lg:ml-auto">
				<a href="#" class="kt-btn kt-btn-primary">
					<i class="ki-filled ki-pencil"></i>
					Edit Profile
				</a>
				<button class="kt-btn kt-btn-outline">
					<i class="ki-filled ki-setting-2"></i>
					Settings
				</button>
			</div>
		</div>
	</div>

	<!-- Profile Content Grid -->
	<div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
		<!-- Left Column - Profile Information -->
		<div class="lg:col-span-2">
			<div class="grid gap-5 lg:gap-7.5">
				<!-- Personal Information -->
				<div class="kt-card">
					<div class="kt-card-header">
						<h3 class="kt-card-title">Personal Information</h3>
					</div>
					<div class="kt-card-content">
						<div class="grid md:grid-cols-2 gap-5">
							<div class="flex flex-col gap-1.5">
								<label class="text-sm font-medium text-secondary-foreground">Full Name</label>
								<span class="text-sm font-semibold text-mono">John Doe</span>
							</div>
							<div class="flex flex-col gap-1.5">
								<label class="text-sm font-medium text-secondary-foreground">Email Address</label>
								<span class="text-sm font-semibold text-mono">john.doe@example.com</span>
							</div>
							<div class="flex flex-col gap-1.5">
								<label class="text-sm font-medium text-secondary-foreground">Phone Number</label>
								<span class="text-sm font-semibold text-mono">+1 234 567 8900</span>
							</div>
							<div class="flex flex-col gap-1.5">
								<label class="text-sm font-medium text-secondary-foreground">Date of Birth</label>
								<span class="text-sm font-semibold text-mono">January 15, 1990</span>
							</div>
							<div class="flex flex-col gap-1.5">
								<label class="text-sm font-medium text-secondary-foreground">Location</label>
								<span class="text-sm font-semibold text-mono">San Francisco, CA</span>
							</div>
							<div class="flex flex-col gap-1.5">
								<label class="text-sm font-medium text-secondary-foreground">Member Since</label>
								<span class="text-sm font-semibold text-mono">March 2023</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Account Statistics -->
				<div class="kt-card">
					<div class="kt-card-header">
						<h3 class="kt-card-title">Account Statistics</h3>
					</div>
					<div class="kt-card-content">
						<div class="grid md:grid-cols-3 gap-5">
							<div class="flex flex-col items-center text-center p-5 border border-border rounded-lg">
								<i class="ki-filled ki-chart-line text-3xl text-primary mb-3"></i>
								<span class="text-2xl font-bold text-mono">248</span>
								<span class="text-sm text-secondary-foreground">Total Sessions</span>
							</div>
							<div class="flex flex-col items-center text-center p-5 border border-border rounded-lg">
								<i class="ki-filled ki-time text-3xl text-success mb-3"></i>
								<span class="text-2xl font-bold text-mono">156h</span>
								<span class="text-sm text-secondary-foreground">Total Usage</span>
							</div>
							<div class="flex flex-col items-center text-center p-5 border border-border rounded-lg">
								<i class="ki-filled ki-dollar text-3xl text-warning mb-3"></i>
								<span class="text-2xl font-bold text-mono">$2,480</span>
								<span class="text-sm text-secondary-foreground">Total Spent</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Recent Activity -->
				<div class="kt-card">
					<div class="kt-card-header">
						<h3 class="kt-card-title">Recent Activity</h3>
					</div>
					<div class="kt-card-content">
						<div class="flex flex-col gap-4">
							<div class="flex items-start gap-3 pb-4 border-b border-border">
								<div class="flex-shrink-0">
									<div class="size-10 rounded-full bg-primary/10 flex items-center justify-center">
										<i class="ki-filled ki-check text-primary"></i>
									</div>
								</div>
								<div class="flex-1">
									<p class="text-sm font-medium text-mono">Payment Successful</p>
									<p class="text-sm text-secondary-foreground">Monthly subscription payment of $29.99</p>
									<span class="text-xs text-muted-foreground">2 hours ago</span>
								</div>
							</div>
							<div class="flex items-start gap-3 pb-4 border-b border-border">
								<div class="flex-shrink-0">
									<div class="size-10 rounded-full bg-success/10 flex items-center justify-center">
										<i class="ki-filled ki-user text-success"></i>
									</div>
								</div>
								<div class="flex-1">
									<p class="text-sm font-medium text-mono">Profile Updated</p>
									<p class="text-sm text-secondary-foreground">You updated your profile information</p>
									<span class="text-xs text-muted-foreground">1 day ago</span>
								</div>
							</div>
							<div class="flex items-start gap-3">
								<div class="flex-shrink-0">
									<div class="size-10 rounded-full bg-warning/10 flex items-center justify-center">
										<i class="ki-filled ki-security-user text-warning"></i>
									</div>
								</div>
								<div class="flex-1">
									<p class="text-sm font-medium text-mono">Security Settings Changed</p>
									<p class="text-sm text-secondary-foreground">Two-factor authentication enabled</p>
									<span class="text-xs text-muted-foreground">3 days ago</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Right Column - Additional Info -->
		<div class="lg:col-span-1">
			<div class="grid gap-5 lg:gap-7.5">
				<!-- Current Plan -->
				<div class="kt-card">
					<div class="kt-card-header">
						<h3 class="kt-card-title">Current Plan</h3>
					</div>
					<div class="kt-card-content">
						<div class="flex flex-col gap-4">
							<div class="flex items-center justify-between p-4 bg-primary/5 rounded-lg border border-primary/20">
								<div>
									<h4 class="font-semibold text-mono">Premium Plan</h4>
									<p class="text-sm text-secondary-foreground">Unlimited access</p>
								</div>
								<span class="text-xl font-bold text-primary">$29.99</span>
							</div>
							<div class="flex flex-col gap-2">
								<div class="flex items-center justify-between text-sm">
									<span class="text-secondary-foreground">Billing Cycle</span>
									<span class="font-medium text-mono">Monthly</span>
								</div>
								<div class="flex items-center justify-between text-sm">
									<span class="text-secondary-foreground">Next Billing</span>
									<span class="font-medium text-mono">Jan 15, 2025</span>
								</div>
								<div class="flex items-center justify-between text-sm">
									<span class="text-secondary-foreground">Auto Renewal</span>
									<span class="font-medium text-success">Enabled</span>
								</div>
							</div>
							<button class="kt-btn kt-btn-outline kt-btn-sm w-full">
								Upgrade Plan
							</button>
						</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="kt-card">
					<div class="kt-card-header">
						<h3 class="kt-card-title">Quick Actions</h3>
					</div>
					<div class="kt-card-content">
						<div class="flex flex-col gap-2">
							<a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-transparent transition">
								<i class="ki-filled ki-key text-lg text-primary"></i>
								<span class="text-sm font-medium text-mono">Change Password</span>
							</a>
							<a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-transparent transition">
								<i class="ki-filled ki-security-user text-lg text-success"></i>
								<span class="text-sm font-medium text-mono">Security Settings</span>
							</a>
							<a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-transparent transition">
								<i class="ki-filled ki-notification text-lg text-warning"></i>
								<span class="text-sm font-medium text-mono">Notifications</span>
							</a>
							<a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-transparent transition">
								<i class="ki-filled ki-cheque text-lg text-info"></i>
								<span class="text-sm font-medium text-mono">Billing History</span>
							</a>
						</div>
					</div>
				</div>

				<!-- Preferences -->
				<div class="kt-card">
					<div class="kt-card-header">
						<h3 class="kt-card-title">Preferences</h3>
					</div>
					<div class="kt-card-content">
						<div class="flex flex-col gap-4">
							<label class="flex items-center justify-between">
								<div class="flex flex-col gap-1">
									<span class="text-sm font-medium text-mono">Email Notifications</span>
									<span class="text-xs text-secondary-foreground">Receive updates via email</span>
								</div>
								<input checked class="kt-switch kt-switch-sm" type="checkbox">
							</label>
							<label class="flex items-center justify-between">
								<div class="flex flex-col gap-1">
									<span class="text-sm font-medium text-mono">SMS Alerts</span>
									<span class="text-xs text-secondary-foreground">Get important alerts via SMS</span>
								</div>
								<input class="kt-switch kt-switch-sm" type="checkbox">
							</label>
							<label class="flex items-center justify-between">
								<div class="flex flex-col gap-1">
									<span class="text-sm font-medium text-mono">Marketing Emails</span>
									<span class="text-xs text-secondary-foreground">Receive promotional content</span>
								</div>
								<input checked class="kt-switch kt-switch-sm" type="checkbox">
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
