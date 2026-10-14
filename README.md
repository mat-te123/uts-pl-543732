# vacant-app

## BACKEND DOCUMENTATION

tree main API endpoint

```
http://localhost:8000/api/vacants
```

responds

```
{
    "Status": 200,
    "Massage": "Success",
    "vacants": [
        {
            "id": 3,
            "vacant_name": "Software Eng",
            "vacant_desc": "PTFI",
            "vacant_link": "https://www.youtube.com/watch?v=p7Igl9dwOWE&pp=ugUEEgJlbg%3D%3D",
            "created_at": "2026-10-09T19:07:34.000000Z",
            "updated_at": "2026-10-09T19:07:34.000000Z"
        }
    ]
}
```

```
http://localhost:8000/api/vacant/post
```

responds

```
{
    "Status": 200,
    "Massage": "Success added data"
}
```
