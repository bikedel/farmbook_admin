
<h1>ProteaDB</h1>

<img src="http://www.proteadb.co.za/farmbooks/storage/imports/farmbook.jpeg" alt="Farmbooks" style="width:600px;height:228px;">
Click here to reset your password: <a href="{{ $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a>
