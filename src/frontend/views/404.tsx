import {Link} from "react-router";

export function NotFoundPage() {
  return (
    <div className="text-center">
      <h1 className="text-4xl">404</h1>
      <p className="text-3xl">Not found</p>
      <Link to="/">
        Go home
      </Link>
    </div>
  )
}