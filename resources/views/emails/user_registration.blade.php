{{-- @include("emails.header")

<p>Dear {{ $user }},</p>
<p>Thank you for joining us.</p>
<p>Your Email Verification Code is {{ $code }}</p>

@include("emails.footer") --}}

<!DOCTYPE html>
  <html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
		<title>Email project template</title>
	</head>
	<body style="margin: 0;padding: 0;">
		<table style="width: 600px;margin: auto;padding: 0;" cellpadding="0"cellspacing="0">
			<tbody>
				<tr>
					<td>
						<div style="text-align: center;background-color: #f7f7f7;padding: 50px;margin-bottom: 20px;">
                            <img style="width: 30%;" src="{{ asset('assets/img/logo.png') }}">
						</div>
						   <table style="margin-left: 85px;width: 65%; border-bottom: 1px solid #d2dbe5">
							<tbody>
								<tr>
									<td>
										<h1 style="color: #2f4058;font-size: 35px;font-family: 'Roboto', sans-serif;font-weight: 400; margin-bottom: 0;">Thanks for Signing Up</h1>
									</td>
								</tr>
								<tr>
									<td>
										<p style="color: #2f4058;font-size: 20px;margin-top: 0px;margin-bottom: 10px;font-family: 'Roboto', sans-serif;font-weight: 400;">Verify your email address</p>
									</td>
								</tr>
								<tr>
									<td>
										<h2 style="color: #2f4058;font-size: 20px;font-family: 'Roboto', sans-serif;font-weight: bold;">Hi, {{ $user }}</h2>
									</td>
								</tr>
								<tr>
									<td>
										<p style="color: #2f4058;font-size: 15px;margin-bottom: 25px;font-family: 'Roboto', sans-serif;font-weight: 400;width: 95%;">You’re almost ready to get started. Please enter the code below on the app to proceed</p>
									</td>
								</tr>
								<tr>
									<td>
                                        <h3 style="color: white;font-size: 30px;font-family: 'Roboto', sans-serif;border: none; font-weight: 500; text-align: center;background-color: #33394C;padding: 5px;border-radius: 7px;margin-bottom: 25px; margin-top: 0px;margin-bottom: 25px;">{{ $code }}</h3>
										{{-- <h3 style="color: #2f4058;font-size: 30px;font-family: 'Roboto', sans-serif;font-weight: 500; margin-top: 0px;margin-bottom: 5px;">{{ $code }}</h3> --}}
									</td>
								</tr>
								{{-- <tr>
									<td>
										<p style="color: #2f4058;font-size: 13px;font-family: 'Roboto', sans-serif;font-weight: 400; margin-top: 0px;">This will expire at 17 Nov 2021, 03:00 pm</p>
									</td>
								</tr> --}}
								<tr>
									<td>
										<p style="color: #2f4058;font-family: 'Roboto', sans-serif;font-weight: 300; font-size: 15px;margin-bottom: 8px;">Thanks,</p>
									</td>
								</tr>
								<tr>
									<td>
										<h4 style="color: #2f4058;font-size: 20px;margin-top: 0;margin-bottom: 30px;font-family: 'Roboto', sans-serif;font-weight: 500;">Sijily</h4>
									</td>
								</tr>
							</tbody>
						</table>
						<table style="margin-left:85px;">
							<tbody>
								<tr>
									<td>
										<h5 style="color: #2f4058;font-size: 18px; margin-bottom: 0;font-family: 'Roboto', sans-serif;font-weight: 600;">Get in touch</h5>
									</td>
								</tr>
								<tr>
									<td>
										<p style="color: #2f4058;font-family: 'Roboto', sans-serif;font-weight: 400; font-size: 15px; margin-top: 8px;">customersupport@sijily.net</p>
									</td>
								</tr>
								<tr>
									<td style="display: inline-flex;">
										<div>
											<a href="#"><img style="width: 35px;margin-right: 25px;" src="{{ asset('assets/img/twiter.png') }}"></a>
										</div>

										<div>
											<a href="#"><img style="width: 35px;margin-right: 25px;" src="{{ asset('assets/img/instragram.png') }}"></a>
										</div>

										<div>
											<a href="#"><img style="width: 35px;" src="{{ asset('assets/img/facebook.png') }}"></a>
										</div>

									</td>
								</tr>
								<tr>
									<td>
										<p style="color: #2f4058;font-size: 13px;font-family: 'Roboto', sans-serif;font-weight: 400;">Copyrights 2021 © Sijily all rights reserved</p>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>





