
namespace java org.fusioproject.worker.generated
namespace php Fusio.Worker.Generated

/**
 * The Fusio Worker provides a simple interface so that the Fusio instance can interact with the worker.
 */
service Worker {

  /**
   * Sets a specific connection to the worker. This method is invoked everytime a connection is created or updated at
   * the Fusio instance. The worker must persist the connection so at it can be reused on execution
   */
  Message setConnection(1: Connection connection),

  /**
   * Sets a specific action to the worker. This method is invoked everytime an action is created or updated at the Fusio
   * instance. The worker must persist the action code at a file which then can be executed. If your language needs a
   * compile step it should be invoked at this call
   */
  Message setAction(1: Action action),

  /**
   * Is called if an user invokes a route at Fusio and this routes has a worker action assigned. The worker must then
   * execute the provided action name and return the response
   */
  Result executeAction(1: Execute execute)

}

struct Message {
  1: bool success,
  2: string message
}

struct Connection {
  1: string name,
  2: string type,
  3: map<string, string> config
}

struct Action {
  1: string name,
  2: string code
}

struct Execute {
  1: string action,
  2: Request request,
  3: Context context
}

union Request {
  1: HttpRequest http,
  2: RpcRequest rpc
}

struct HttpRequest {
  1: string method,
  2: map<string, string> headers,
  3: map<string, string> uriFragments,
  4: map<string, string> parameters,
  5: string body
}

struct RpcRequest {
  1: string arguments
}

struct Context {
  1: i64 routeId,
  2: string baseUrl,
  3: App app,
  4: User user
}

struct App {
  1: i64 id,
  2: i64 userId,
  3: i32 status,
  4: string name,
  5: string url,
  6: string appKey,
  7: list<string> scopes,
  8: list<string> parameters
}

struct User {
  1: i64 id,
  2: i64 roleId,
  3: i64 categoryId,
  4: i32 status,
  5: string name,
  6: string email,
  7: i32 points
}

struct Result {
  1: Response response,
  2: list<Event> events,
  3: list<Log> logs
}

struct Response {
  1: i32 statusCode,
  2: map<string, string> headers,
  3: string body
}

struct Event {
  1: string eventName,
  2: string data
}

struct Log {
  1: string level,
  2: string message
}
