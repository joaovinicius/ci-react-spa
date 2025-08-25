import {Outlet} from "react-router";

export function Page() {
  return <>
    <h1>Page</h1>

    {/*<Render config={config} data={data} />;*/}
    <Outlet />
  </>
}